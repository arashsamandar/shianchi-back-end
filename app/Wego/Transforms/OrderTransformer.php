<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 02/10/17
 * Time: 12:13
 */

namespace Wego\Transforms;


use App\Order;
use App\Payment;
use App\Wego\Buy\Payment\Online;
use Carbon\Carbon;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;
use Wego\ShamsiCalender\Shamsi;

class OrderTransformer extends TransformerAbstract
{
    use TransformerHelper;

    protected $fields;

    protected $validParams = ['status'];
    protected $availableIncludes = [
        'address',
        'products'
    ];

    protected $defaultIncludes = [
        'address',
        'products'
    ];

    function __construct($fields = null)
    {
        $this->fields = $fields;
    }

    public function transform(Order $order)
    {
        $url = $this->getPaymentUrl($order);
        if(is_null($order->ac)){
            if ($order->shipping_company == 'WegoJet'){
                $count = Order::where('id','<=',$order->id)
                    ->where('shipping_company','WegoJet')->count();
            } else {
                $count = Order::where('id','<=',$order->id)
                    ->where('shipping_company','<>','WegoJet')->count();
            }
            $addition = '0';
            if($count % 2 == 1){
                $addition = '1';
            }
        } else {
            $addition = $order->ac;
        }
        $inPlacePrice =  ($order->created_at->gt(Carbon::parse("2019-02-17 17:30:00"))) ? 12000 : 0;
        return $this->transformWithFieldFilter([
            'id' => $order->id.'-'.$addition,
            'status' => $order->status,
            'delivery_time' => $order->delivery_time,
            'shipping_company' => $order->shipping_company,
            'shipping_status' => $order->shipping_status,
            'shipping_price' => $order->shipping_price,
            'final_products_price' => $order->final_products_price,
            'final_order_price' => ($order->payment_id == Payment::ONLINE && $order->progressable) ? $order->final_order_price : ($order->final_order_price+$inPlacePrice),
            'total_discount' => $order->total_discount,
            'created_at' => $order->created_at->toDateTimeString(),
            'shamsi_day' => !strpos($order->delivery_time,'&') ? "" : Shamsi::timeDetail(Carbon::parse(explode('&',$order->delivery_time)[0]))['weekday'],
            'progressable'=> boolval($order->progressable),
            'payment_id' => $order->payment_id,
            'user_id'=>$order->user_id,
            'payment_url' => $url,
            'days' => Carbon::now()->diffInDays(Carbon::parse($order->created_at)),
            'description'=> $order->description,
            'isInPlace' => (($order->payment_id == Payment::ONLINE && $order->progressable) || $order->created_at->lt(Carbon::parse("2019-02-17 17:30:00"))) ? 0 : 1

        ], $this->fields);
    }

    public function includeAddress(Order $order)
    {
        $address = $order->address;
        return $this->item($address, new AddressTransformer($order->address));
    }

    public function includeProducts(Order $order)
    {
        $products = $order->products;
        return $this->collection($products, new ProductDetailTransformer());
    }

    private function getPaymentUrl($order)
    {
        if ($order->status == Order::PURCHASED || $order->status == Order::DELIVERED) {
            $url = (new Online())
                ->setUserId($order->user_id)
                ->setToken(request()->get('token'))
                ->setOrder($order)
                ->getUrl();
            return ($order->progressable && $order->payment_id == Payment::ONLINE) ? '' : $url;
        } else {
            return "http://shiii.ir/buyer-profile/buy";
        }
    }

}