<?php

namespace App\Http\Controllers;

use App\BuyerAddress;
use App\Events\OrderDeleted;
use App\Events\OrderShipped;
use App\Events\OrderStatusSetToCanceled;
use App\Events\OrderStatusSetToSent;
use App\Events\OrderUpdated;
use App\Holiday;
use App\Http\Requests\setOrderToUnavailableRequest;
use App\Jobs\SendPaymentSms;
use App\Order;
use App\OrderProduct;
use App\OrganizationDetails;
use App\Payment;
use App\Product;
use App\ProductDetail;
use App\SpecialCondition;
use App\StoreOffer;
use App\User;
use App\Wego\Buy\Payment\Online;
use App\Wego\Buy\Payment\PaymentFactory;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Tymon\JWTAuth\Facades\JWTAuth;
use Wego\Buy\BuyStorageUtil;
use Wego\Buy\Order as OrderGenerator;
use Wego\Shipping\Company\Wegobazaar;
use Wego\Transforms\NotificationTransformer;
use Wego\Transforms\OrderOfferDetails;
use Wego\Transforms\OrderTransformer;
use Wego\Transforms\ByStoreOrderTransformer;

class OrderController extends ApiController
{
    use Helpers;
    const ORDER_NUMBER_LIMIT = 25;

    public function store()
    {
//        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
//            // Ignores notices and reports all other kinds... and warnings
//            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//            //     error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
//        }
        $user = $this->auth->user();
        $order = (new OrderGenerator(request()->get('products'), collect(request()->get('order'))))
            ->setUser($user)
            ->setCouponId(request()->get('coupon'))
            ->generate();
        if(request()->has('organization')){
            $organizationData = request()->organization;
            $organizationData['order_id'] = $order->id;
            OrganizationDetails::create($organizationData);
        }
        event(new OrderShipped($order));

        $url = PaymentFactory::getPayment($order->payment_id)
            ->setOrder($order)
            ->setUserId($user->id)
            ->setToken(request()->input('token'))
            ->getUrl();

        return $this->respondOk($url, 'path');
    }

    public function delete($id)
    {

        $order = Order::find($id);
        $orderProducts = $order->products;
        $order->delete();
        event(new OrderDeleted($order,$orderProducts));
        return $this->respondOk();
    }

    public function update($id)
    {
//        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
//            // Ignores notices and reports all other kinds... and warnings
//            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//            //     error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
//        }
        $oldOrder = Order::FindOrFail($id);
//        event(new OrderDeleted($oldOrder,$oldOrder->products));
        //$oldOrder->delete();
//        dd('h');
        if(empty(request()->get('products'))){
            return (new ApiController())->setStatusCode(404)->respondWithError("لطفا کالای جایگزین در سفارش را وارد کنید.");
        }
        $order= null;
        $user = User::findOrFail($oldOrder->user_id);
        $oldDeliveryTime = $oldOrder->delivery_time;
        DB::transaction(function () use ($id, $user,&$order,$oldDeliveryTime) {
            DB::table('order_product')->where('order_id', $id)->delete();
            $order = (new OrderGenerator(request()->get('products'), collect(request()->get('order'))))
                ->setUser($user)
                ->setId($id)
                ->setCouponId(request()->get('coupon'))
                ->checkDeliveryTimeChange($oldDeliveryTime)
                ->generate();
        });
        event(new OrderUpdated($order));

        $url = PaymentFactory::getPayment($order->payment_id)
            ->setOrder($order)
            ->setUserId($user->id)
            ->setToken(request()->input('token'))
            ->getUrl();

        return $this->respondOk($url, 'path');
    }

    public function restoreAddress($id)
    {
        BuyerAddress::withTrashed()
            ->where('id', $id)
            ->restore();
    }

    public function storeOrder()
    {
//        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
//            // Ignores notices and reports all other kinds... and warnings
//            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//            //     error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
//        }
        $storeId = $this->auth->user()->userable_id;

        $order = Order::orderBy('created_at','desc')->whereHas('products', function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        })->paginate(Order::PAGINATE_SIZE);

        return $this->response->paginator($order, (new ByStoreOrderTransformer($storeId)));
    }

    public function buyerOrders()
    {
        $user = $this->auth->user();

        $order = Order::where('id','>',1517657091)->where('user_id',$user->id)->orderBy('created_at','desc')->paginate(Order::PAGINATE_SIZE);

        return $this->response->paginator($order, new OrderTransformer());

    }

    // age status nazad chi?
    public function index()
    {
//        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
//            // Ignores notices and reports all other kinds... and warnings
//            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//            //     error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
//        }
        if (request()->has('status')) {
            $order = Order::byStatus(request()->get('status'))->orderBy('created_at', 'desc')->paginate(Order::PAGINATE_SIZE);
        } else {
            $order = Order::orderBy('created_at', 'desc')->paginate(Order::PAGINATE_SIZE);
        }
        return $this->response->paginator($order, new OrderTransformer());
    }

    public function show($id)
    {
        $order = Order::findOrFail($id);
        return $this->response->item($order, new OrderTransformer());
    }


    public function orderProduct()
    {
//        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
//            // Ignores notices and reports all other kinds... and warnings
//            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//            //     error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
//        }
        $page = request()->page;
        $productStatus =request()->product_status;
        $orders = Order::orderBy('id','desc')->byStatus(request()->status)->with(['products'=>function($query) use($productStatus){
            $query->where('status', (new Product())->convertStatusToNumber($productStatus))->with(['store'=>function($query){
                $query->with('user');
            },'product','color','warranty']);
        }])->whereHas('products',function($query) use($productStatus){
            if(!empty($productStatus)) {
                $query->where('status', (new Product())->convertStatusToNumber($productStatus));
            }
        })->get();
        $analyzedData = $orders->map(function($item){
            $shippingCompany = $item['shipping_company'];
            if(is_null($item['ac'])){
                if ($shippingCompany == 'WegoJet'){
                    $count = Order::where('id','<=',$item['id'])
                        ->where('shipping_company','WegoJet')->count();
                } else {
                    $count = Order::where('id','<=',$item['id'])
                        ->where('shipping_company','<>','WegoJet')->count();
                }
                $addition = '0';
                if($count % 2 == 1){
                    $addition = '1';
                }
            } else {
                $addition = $item['ac'];
            }
            $item['order_id'] = $item['id'].'-'.$addition;
            return $item;
        });
//        return $analyzedData;
        $ali = $analyzedData->map(function($order){
            return $order->products->map(function($item) use($order){
                $item['pivot']['oid'] = $order['order_id'];
                return $item;
            });
        })->flatten();

        $data = $ali->groupBy('pivot.oid');
        $count = $data->count();
        $totalPages = ceil($count/10);
        $result=[];
        $result['data'] = $data->forPage($page,10);
        $result['meta'] = ['current_page'=>$page,'total_pages'=>$totalPages];
        return $result;

    }

    public function searchOrderProduct()
    {
        $productStatus = request()->product_status;
        $order_id = request()->order_id;
        $orders = Order::where('id','like',"%".$order_id)->byStatus(request()->status)->with(['products'=>function($query) use($productStatus){
            $query->where('status', (new Product())->convertStatusToNumber($productStatus))->with(['store'=>function($query){
                $query->with('user');
            },'product','color','warranty']);
        }])->whereHas('products',function($query) use($productStatus){
            if(!empty($productStatus)) {
                $query->where('status', (new Product())->convertStatusToNumber($productStatus));
            }
        })->get();
        $analyzedData = $orders->map(function($item){
            $shippingCompany = $item['shipping_company'];
            if ($shippingCompany == 'WegoJet'){
                $count = Order::where('id','<=',$item['id'])
                    ->where('shipping_company','WegoJet')->count();
            } else {
                $count = Order::where('id','<=',$item['id'])
                    ->where('shipping_company','<>','WegoJet')->count();
            }
            $addition = '0';
            if($count % 2 == 1){
                $addition = '1';
            }
            $item['order_id'] = $item['id'].'-'.$addition;
            return $item;
        });
//        return $analyzedData;
        $ali = $analyzedData->map(function($order){
            return $order->products->map(function($item) use($order){
                $item['pivot']['oid'] = $order['order_id'];
                return $item;
            });
        })->flatten();

        $data = $ali->groupBy('pivot.oid');
        $result=[];
        $result['data'] = $data;
        $result['meta'] = ['current_page'=>1,'total_pages'=>1];
        return $result;

    }

    public function searchOrders()
    {
        $orderId = request()->order_id;
        if (request()->has('status')) {
            $order = Order::where('id','like','%'.$orderId)->byStatus(request()->get('status'))->orderBy('created_at', 'desc')->paginate(Order::PAGINATE_SIZE);
        } else {
            $order = Order::where('id','like','%'.$orderId)->orderBy('created_at', 'desc')->paginate(Order::PAGINATE_SIZE);
        }
        return $this->response->paginator($order, new OrderTransformer());
    }

    public function orderStatus($id)
    {
        //        event(new OrderStatusSetToPurchased($order)); if purchased
//        if(request()->get('status') == 'Cancel'){
//            $order = Order::find($id);
//            event(new OrderDeleted($order,$order->products));
//        }
        Order::where('id',$id)->updateStatus(request()->get('status'));
        if(request()->get('status') == "Delivered"){
            $id= str_replace("-0","",$id);
            $id =str_replace("-1","",$id);
            event(new OrderStatusSetToSent($id));
        } elseif (request()->get('status') == 'Cancel'){
            $id= str_replace("-0","",$id);
            $id =str_replace("-1","",$id);
//            $this->checkIfHoliday($id);
            if (request()->sms==1) {
                event(new OrderStatusSetToCanceled($id));
            }
        }
        return $this->setStatusCode(200)->respondOk("order status updated");
    }
    public function orderProductStatus()
    {
        $order_id = request()->order_id;
        $detail_id = request()->detail_id;
        $status = request()->status;
        $order = Order::findOrFail($order_id);
        $order->updateProductsStatus($detail_id, $status);
    }

    public function setOrderProductStatusToUnavailable(setOrderToUnavailableRequest $request)
    {
        $orderId = $request->input('order_id');
        $productId = $request->input('product_id');
        $message = $request->input('message');
        $orderAfterChangeProductStatus = BuyStorageUtil::setOrderProductStatus($orderId, $productId, Product::UNAVAILABLE);
        $orderAfterAddStaffMessage = BuyStorageUtil::addBazaarStaffMessageToOrder($orderAfterChangeProductStatus, $productId, $message);
        BuyStorageUtil::updateOrder($orderAfterAddStaffMessage);
        Product::setToZeroQuantity($productId);
        return $this->setStatusCode(200)->respondOk("product is set to unavailable");
    }

    public function getOrderById($id)
    {
        $orderId= decrypt($id);
        $order = Order::find($orderId)->toArray();
        return $order;
    }

    public function finishOperation($id)
    {
        $orderId = decrypt($id);
        Order::where('id',$orderId)->update(['progressable'=>true,'payment_id'=>Payment::ONLINE]);
    }

    public function setHoliday()
    {
        Holiday::create(request()->all());
        return $this->respondOk();
    }

    public function setOldOrdersStatus()
    {
        Order::where('created_at','<',Carbon::now()->subDays(3)->subHours(10))->where('status',Order::IN_PROGRESS)->update(['status'=>Order::DELIVERED]);
    }

    public function getOrderSmsToMyNumber($id)
    {
        $order = Order::find($id);
        if ($order->payment_id != Payment::ONLINE || $order->progressable == 'false') {
            $job = (new SendPaymentSms($order))->delay(Carbon::now()->addSeconds(20));
            dispatch($job);
        }
    }

    public function paymentUrl($id)
    {
        $order = Order::find($id);
        $token = JWTAuth::fromUser($order->user);
        dump('https://api.wegobazaar.com/pay/'.encrypt($id));
        $url = (new Online())
            ->setUserId($order->user_id)
            ->setToken($token)
            ->setOrder($order)
            ->getUrl();
        $url = $this->shorten($url);
        return $url;
    }

    public function pay($id)
    {
        $orderId = decrypt($id);
        $order = Order::find($orderId);
        $token = JWTAuth::fromUser($order->user);
        $url = (new Online())
            ->setUserId($order->user_id)
            ->setToken($token)
            ->setOrder($order)
            ->getUrl();
//        $url = $this->shorten($url);
        if ($order->status != Order::CANCELLED){
            return Redirect::to($url);
        } else{
            return Redirect::to("http://shiii.ir");
        }
    }

    public function checkOrderPrices()
    {
        $priceNotChanged = true;
        foreach(request()->products as $product){
            $detail = ProductDetail::find($product['detail_id']);
            $special = $detail->special_conditions->where('status',SpecialCondition::AVAILABLE)->where("type","discount")->first();
            if(!is_null($special)){
                $price = $detail->current_price - $special->amount;
            } else {
                $price = $detail->current_price;
            }
            if ($price != $product['price']){
                $priceNotChanged = false;
            }
        }
        return $this->respondOk($priceNotChanged,"notChanged");
    }
    public function shorten($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"api.yon.ir/?url=".$url);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $content = \GuzzleHttp\json_decode($content,true);
        curl_close($ch);
        $shortenUrl = "http://yon.ir/".$content['output'];
        return $shortenUrl;
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL,"https://www.googleapis.com/urlshortener/v1/url?key=AIzaSyAzrk5CZE2i-HTHuYxea6rdGppZX2o3oWM");
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS,
//            json_encode(["longUrl"=>$url]));
//        curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json"));
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        $server_output = curl_exec ($ch);
//        curl_close ($ch);
//        $output = \GuzzleHttp\json_decode($server_output,true);
//        return $output['id'];
    }

    public function getOrdersForApp()
    {
        $user = $this->auth->user();
        $store = $user->userable;
        $op = OrderProduct::orderBy('created_at', 'desc')
            ->where('created_at','>',Carbon::now()->subDay(4))->get();
        $op = $op->filter(function($orderProduct) use($store){
            $storeBrands = $store->brands;
            $detail = ProductDetail::find($orderProduct->detail_id);
            $storeCat = DB::table('category_store')->where('category_id',$detail->product->category_id)
                ->where('store_id',$store->id)->first();
            if(!empty($storeCat)) {
                if (!empty($storeBrands) && !empty($detail->product->brand_id)) {
                    $foundedBrand = $storeBrands->where('id', $detail->product->brand_id);
                    if (!empty($foundedBrand)) {
                        return true;
                    }
                } else {
                    return true;
                }
            }

        });
//        dd($op->all());
//        $storeBrands = $store->brands;
//        if(!empty($storeBrands) && !empty($productDetail->product->brand_id)){
//            $foundedBrand = $storeBrands->where('id',$productDetail->product->brand_id);
//            if (!empty($foundedBrand)){
//                // send notification to store
//            }
//        } else {
//            // send notification to store
//        }
        return $this->response->collection($op,new NotificationTransformer($user->userable_id));
    }

    public function getOfferDetailForApp($id)
    {
        $op = OrderProduct::find($id);
        return $this->response->item($op,new OrderOfferDetails(10));
    }

    public function getUserToken($id)
    {
        $orderId = decrypt($id);
        $order = Order::find($orderId);
        $token = JWTAuth::fromUser($order->user);
        return $token;
    }

    public function submitOffer()
    {
        $user = $this->auth->user();
        $data = array_except(request()->all(),['token']);
        $data['store_id']= $user->userable_id;
        $storeOffer = StoreOffer::where('order_product_id',request()->order_product_id)
            ->where('store_id',$user->userable_id)->first();
        if(empty($storeOffer)) {
            StoreOffer::create($data);
        } else {
            $storeOffer->store_price = request()->store_price;
            $storeOffer->save();
        }
        return $this->respondOk();
    }

    public function transformOrdersToNewStyle()
    {

        $params = [
            'index' => 'wego_1' ,
            'type' => 'orders',
            'body' => [
                'query' => [
                    'match_all' => []
                ]
            ],
            'size'=>2000
        ];

        $client = ClientBuilder::create()->build();
        $result = $client->search($params);
        foreach ($result['hits']['hits'] as $order) {
            $sqlOrder = Order::find($order['_source']['id']);
            if (is_null($sqlOrder))
                continue;
            try {
                $sqlOrder->delivery_time = empty($order['_source']['delivery_time']) ? $order['_source']['delivery_date'] : $order['_source']['delivery_date'] . '&' . $order['_source']['delivery_time'];
                $sqlOrder->address_id = $order['_source']['address']['id'];
                $sqlOrder->progressable = $order['_source']['progressable'];
                $sqlOrder->shipping_company = $order['_source']['shipping_company'];
                $sqlOrder->shipping_status = $order['_source']['shipping_status'];
                $sqlOrder->shipping_price = $order['_source']['shipping_price'];
                $sqlOrder->final_products_price = $order['_source']['total_price'];
                $sqlOrder->final_order_price = $order['_source']['final_price'];
                $sqlOrder->payment_id = $order['_source']['payment_id'];
                $sqlOrder->total_discount = $order['_source']['total_discount'];
                $sqlOrder->coupon_id = (empty($order['_source']['coupon'])) ? null : $order['_source']['coupon']['id'];
                $sqlOrder->save();
            } catch (\Exception $e){
                continue;
            }
            foreach ($order['_source']['stores'] as $store) {
                foreach ($store['products'] as $product) {
                    if (empty($product['color_id'])) {
                        $detail = ProductDetail::where('product_id', $product['product_id'])->first();
                    } else {
                        $detail = ProductDetail::where('product_id', $product['product_id'])
                            ->where('color_id', $product['color_id'])->first();
                    }
                    try {
                        DB::table('order_product')->where('product_id', $product['product_id'])
                            ->update(['detail_id' => $detail->id, 'status' => $product['status']]);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }
    }

    private function checkIfHoliday($id)
    {
        $order = Order::find($id);
        if ($order->shipping_company == 'Wegobazaar') {
            $wegobazaar['time'] = $order->delivery_time;
            $time = explode('&', $wegobazaar['time']);
            $date = Carbon::parse($time[0]);
            $orderLimit = 13;
            if($date->dayOfWeek == 4 || $date->dayOfWeek == 6){
                $orderLimit = 5;
            }
            $wegobazaarOrders = Order::where('shipping_company', 'wegobazaar')
                ->where('delivery_time', 'like', '%' . $date->toDateString() . '%')
                ->where('status', '<>', Order::CANCELLED)->count();
            $count = $wegobazaarOrders;
            if ($count < $orderLimit) {
                $holiday = Holiday::where('holiday', $date->toDateString())->first();
                if (!is_null($holiday)) {
                    $holiday->delete();
                }
            }
        }
    }
}
