<?php

namespace App\Http\Controllers;

use App\Category;
use App\Events\ProductAdded;
use App\Events\ProductWatched;
use App\Http\Requests\comments\SetNotConfirmedCommentRequest;
use App\Http\Requests\DeletePictureRequest;
use App\Http\Requests\ProductRequest;
use App\OutsideOrder;
use App\Product;
use App\ProductDetail;
use App\ProductPicture;
use App\SpecialCondition;
use App\Store;
use App\Warranty;
use App\Wego\Services\Telegram\StoreProductCard;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kavenegar\KavenegarApi;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Wego\DeliveryTimeCalculator;
use Wego\ElasticHelper;
use Wego\Helpers\JsonUtil;
use Wego\Helpers\PersianUtil;
use Wego\Product\ProductEditor;
use Wego\Product\ProductFactory;
use Wego\Product\ProductParser;
use Wego\PictureHandler;
use Wego\Product\Specials\CategoryProducts;
use Wego\Product\Specials\ItemBaseRecommendation;
use Wego\Product\Specials\RandomFromTopCategories;
use Wego\Product\Specials\SpecialFactory;
use Wego\Search\ElasticQueryMaker;
use Wego\Search\ProductElasticSearch;
use Wego\ShamsiCalender\Shamsi;
use Wego\UserHandle\UserPermission;

class ProductController extends ApiController
{
    use Helpers;
    const PAGINATE_NUMBER = 15;
    protected $productFactory, $product;
    protected $requestRules;
    protected $deleteRequestRules = [
        "path" => "required",
    ];

    function __construct(ProductFactory $productFactory = null, Product $product)
    {
        $this->productFactory = $productFactory;
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getPreConfirmedProducts()
    {
        return Product::byStatus(Product::PRE_CONFIRMATION);
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getNotConfirmedProducts()
    {
        return Product::byRejectionMessage()->byStatus(Product::NOT_CONFIRMED);
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getNotConfirmedProductsByStore()
    {
        $storeId = $this->auth->user()->userable_id;
        return Product::byRejectionMessage()->byStore($storeId)->byStatus(Product::NOT_CONFIRMED);
    }

    /**
     * @return mixed
     */
    public function getPreConfirmedProductsByStore()
    {
        $storeId = $this->auth->user()->userable_id;
        return Product::byStore($storeId)->byStatus(Product::PRE_CONFIRMATION);
    }
    /**
     * @param ProductRequest $request
     * @return mixed
     */
    public function store(ProductRequest $request)
    {
        $user = $this->auth->user();
        $product = $this->productFactory->handle($request->all(), $user);

        if ($product->id) {
            event(new ProductAdded($product));
            return $this->respondOk($product->id, 'product_id');
        }
        return $this->respondNotFound('product cannot inserted properly');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getProductsByCategory(Request $request)
    {
        $storeId = $this->auth->user()->userable_id;
        $products = Product::byCategoriesByStore($storeId, $request->category_id);
        return $this->respondArray($products);
    }

    public function searchStoreProducts(Request $request)
    {
        $storeId = $this->auth->user()->userable_id;
        $name = $request->name;
        $nameQuery = "%".$name."%";
        $products = Product::where(function($query) use($nameQuery,$name){
            $query->where('english_name','like',$nameQuery)->orWhere('persian_name','like',$nameQuery)
                ->orWhere('id',$name);
        })->byStoreDetail($storeId)->bystatus(Product::CONFIRMED);
        return $this->respondArray($products);
    }

    public function getAllProductDetails($id)
    {
        $type = request()->type;
        $products = Product::where('id',$id)->byDetails($type)->get();
        $products->each(function($node){
           $node->product_details->each(function($detail){
              $detail->makeVisible('uid');
           });
        });
        return $this->respondArray($products);
    }

    public function updatePrice(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('view',$product);
        Product::updatePrice($product, $request->current_price);
        return $this->respondOk();
    }

    public function updateQuantity(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('view',$product);
        Product::updateQuantity($product, $request->quantity);
        return $this->respondOk();
    }
    /**
     * @param ProductRequest $request
     * @param $id
     * @return mixed
     */
    public function update(ProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('view',$product);
        $productEditor = new ProductEditor;
        $productEditor->update($request->toArray(), $id);
        (new Product())->setToConfirmed($id);
        return $this->setStatusCode(200)->respondOk("product is updated");
    }

    public static function changeUpdateProductStyle($inputs = [])
    {
        if (!isset($inputs['special']))
            $inputs['special'] = [];
        return $inputs;
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function setToConfirmed($productId)
    {
        $this->product->setToConfirmed($productId);
        return $this->respondOk('product is set to confirmed');
    }

    //TODO: in kamel nist chun hameye dastan haro pak nemikone albate chun ke mikhaym pak e vagheE nakonim bad ham nist
    // in tori har vaght khastim kheyli rahat mitunim mahsul o bargardunim

    /**
     * @param $productId
     * @return mixed
     */
    public function deleteConfirmedProduct($productId)
    {
        $this->product->deleteIndexedProduct($productId);
        return $this->respondOk('delete successfully');
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function deleteNotConfirmedProduct($productId)
    {
        $product = Product::find($productId);
        $pictures = $product->pictures;
        foreach($pictures as $picture){
            (new PictureHandler())->deletePicturesFromFile($product,$picture->path);
        }
        Product::deleteById($productId);
        return $this->respondOk('delete successfully');
    }

    /**
     * @param SetNotConfirmedCommentRequest $request
     * @param $productId
     * @return mixed
     */
    public function setToNotConfirmed(SetNotConfirmedCommentRequest $request, $productId)
    {
        $product = $this->product->setConfirmationStatus($productId, Product::NOT_CONFIRMED);
        $message = $request->message;
        $product->rejectionMessages()->create([
            'message' => $message
        ]);
        return $this->respondOk('product is set to not confirmed');
    }

    /**
     * @param $picPath
     * @return mixed
     */
    private function findProductIdFromPicPath($picPath)
    {
        preg_match('/.*product([0-9]*)/', $picPath, $result);
        return $result[1];
    }

    /**
     * in tabe checkhaie ghabl az delete o baresi mikone age delete too temp nabash az DB ham pak mikone
     * @param DeletePictureRequest $request
     * @return mixed
     */
    public function deletePicture(DeletePictureRequest $request)
    {
        $user = $this->auth->user();
        $picPath = $request->path;
        if ($this->isTempFile($picPath))
            return $this->respondOk((new PictureHandler())->deleteTempPicture($picPath));
        else
            return $this->deleteStoredProductPicture($user, $picPath);
    }

    /**
     * @param $user
     * @param $picPath
     * @return mixed
     */
    private function deleteStoredProductPicture($user, $picPath)
    {
        $product = $this->checkForStoreAccessToProduct($user, $picPath);
        (new PictureHandler())->deletePicture($picPath, $product);
        $product->confirmation_status = Product::PRE_CONFIRMATION ;
        $product->save();
        return $this->setStatusCode(200)->respondOk("file deleted");
    }

    /**
     * @param $user
     * @param $picPath
     * @return mixed
     */
    private function checkForStoreAccessToProduct($user, $picPath)
    {
        $store = $user->getUserableType();
        $product_id = $this->findProductIdFromPicPath($picPath);
        $product = Product::find($product_id);
        if ($store->user->userable_type == UserPermission::STORE && ($store->id != $product->store_id))
            throw new AccessDeniedException;
        return $product;
    }

    private function isTempFile($picPath)
    {
        return (strpos($picPath, 'temp') !== false);
    }

    /**
     * @param $id
     * @param Request $request
     * @return array query to elastic search
     * query to elastic search
     */
    public function getProductPageJson($id)
    {
        $product = (new ProductElasticSearch())->byId($id);
        $productParser = new ProductParser($product);
        $productDetail = $productParser->parse();
        return $productDetail;
    }

    public function getProductForAmp($id)
    {
        $items=[];
        $items['items'][] = $this->getProductPageJson($id);
        return $items;
    }

    public function productWatched(Request $request)
    {
        $clientId = str_replace("\n", '', $request->client_id);
        event(new ProductWatched($request->product_id, $clientId, $request->ip()));
        return $this->respondOk();
    }

    /**
     * @param $id
     * @return array
     */
    public function getJson($id)
    {
        $productEditor = new ProductEditor();
        $result = $productEditor->getJson($id);
        $result = JsonUtil::convertKeys($result, [
            'special' => 'special_conditions'
        ]);
        return $result;

    }

    /**
     * @param Request $request
     * @return mixed|string
     */
    public function getWatchlistCount(Request $request)
    {
        $productId = $request->product_id;
        $product = Product::findOrFail($productId);
        $this->authorize('view',$product);
        return json_encode(["count" => count($product->stalkers)]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getProductGroup(Request $request)
    {
        $type = $request->type;
        try {
            $specialProducts = SpecialFactory::create($type)->get();
        } catch (\Exception $e) {
            $specialProducts = (new RandomFromTopCategories())->get();
        }
        return $this->respondArray($specialProducts);
    }
    public function lastProductsByStore()
    {
        $productsJson = [];
        $storeId = $this->auth->user()->userable_id;
        $products = Product::where('store_id', $storeId)->orderBy('created_at', 'desc')->get()->take(10);
        foreach ($products as $product) {
            $productsJson[] = JsonUtil::convertKeys((new ProductEditor())->getJson($product->id), [
                'special' => 'special_conditions'
            ]);;
        }
        return $productsJson;
    }

    public function lastProductGroupsByStore()
    {
        $storeId = $this->auth->user()->userable_id;
        $products = Product::where('store_id', $storeId)->orderBy('created_at', 'desc')
            ->get()->take(10)->pluck('category_id');
        $categoryIds = array_unique($products->toArray());
        $categories = Category::whereIn('id', $categoryIds)->get();
        return $categories->toArray();
    }



    public function deleteEmptyPathProductPicture($productId)
    {
        ProductPicture::where('product_id', $productId)->where('path', "")->delete();
        Product::where('id', $productId)->where('confirmation_status', Product::CONFIRMED)->elastic()->get()->addToIndex();
        return $this->respondOk();
    }

    public function changeStoreOfProduct(Request $request)
    {
        $sourceStoreId = $request->sourceId;
        $destinationId = $request->destinationId;
        Product::where('store_id', $sourceStoreId)->where('confirmation_status', Product::CONFIRMED)
            ->update(['store_id' => $destinationId]);
        Product::where('store_id', $destinationId)->where('confirmation_status', Product::CONFIRMED)->elastic()
            ->get()->addToIndex();
        return $this->respondOk();
    }

    public function changeProductStore(Request $request)
    {
        $productIds = $request->product_ids;
        $store_id = $request->store_id;
        $productIds = is_array($productIds) ? $productIds : (array)$productIds;
        Product::whereIn('id', $productIds)->update(['store_id' => $store_id, 'confirmation_status' => Product::CONFIRMED]);
        foreach ($productIds as $productId) {
            $this->product->setToConfirmed($productId);
        }
        return $this->respondOk();
    }

    public function changeProductStoreWithoutConfirming(Request $request)
    {
        $productIds = $request->product_ids;
        $store_id = $request->store_id;
        $productIds = is_array($productIds) ? $productIds : (array)$productIds;
        Product::whereIn('id', $productIds)->update(['store_id' => $store_id]);
        return $this->respondOk();
    }

    public function changeProductBrand(Request $request)
    {
        $productIds = $request->product_ids;
        $brandId = $request->brand_id;
        $productIds = is_array($productIds) ? $productIds : (array)$productIds;
        Product::whereIn('id', $productIds)->update(['brand_id' => $brandId, 'confirmation_status' => Product::CONFIRMED]);
        Product::whereIn('id', $productIds)->elastic()->get()->addToIndex();
        return $this->respondOk();
    }

    public function deleteBrandFromCategory(Request $request)
    {
        $categoryId = $request->category_id;
        $brandId = $request->brand_id;
        DB::table('category_brand')->where('category_id', $categoryId)->where('brand_id', $brandId)->delete();
        SpecificationController::updateElastic($categoryId);
        return $this->respondOk();
    }

    public function deleteOldProductValues(Request $request)
    {
        $productId = $request->product_id;
        $product = Product::find($productId);
        $values = $product->values->pluck('id')->toArray();
        $catSpecs = $product->category->specifications;
        $catValues = [];
        foreach ($catSpecs as $catSpec) {
            $vals = $catSpec->values->pluck('id')->toArray();
            $catValues = array_merge($vals, $catValues);
        }
        foreach ($values as $value) {
            if (array_search($value, $catValues) === false) {
                DB::table('product_value')->where('product_id', $productId)->where('value_id', $value)->delete();
            }
        }
        Product::where('id', $productId)->elastic()->get()->addToIndex();
    }

    public function sendProductMessageToStoreTelegram(Request $request)
    {
        $store = $this->auth->user()->userable;
        $product = Product::byId($request->product_id);
        $this->authorize('view',$product);

        $productCard = new StoreProductCard($product, $store);

        if (Store::HasTelegramChanel($store)) {
            $productCard->generate();
        } else {
            return $this->respondWithError('متاسفانه کانال تلگرامی برای فروشگاه شما یافت نشد');
        }
        return $this->respondOk();
    }

    public function setToNotExistByCategory(Request $request)
    {
        if(!empty($request->category_id) && !empty($request->brand_id)){
            $products = Product::where('category_id', $request->category_id)
                ->where('brand_id', $request->brand_id)->get();
        } elseif(!empty($request->category_id)) {
            $products = Product::where('category_id', $request->category_id)->get();
        } else {
            $products = Product::where('brand_id', $request->brand_id)->get();
        }
        foreach ($products as $product) {
            $flag = false;
            foreach($product->product_details as $detail) {
                if ($detail->quantity > 0) {
                    $flag = true;
                    $detail->quantity = 0;
                    $detail->save();
                }
            }
            if ($flag) {
                Product::where('id', $product->id)->elastic()->addToIndex();
            }
        }
        return $this->respondOk();
    }

    public function setAllToNotExist(Request $request)
    {
        $offset = $request->offset;
        $startId= (1000 * $offset);
        $endId = (1000 * ($offset+1));
        $products = Product::where('id','>',$startId)->where('id','<=',$endId)->get();
        foreach ($products as $product) {
            foreach($product->product_details as $detail) {
                if ($detail->quantity > 0) {
                    $detail->quantity = 0;
                    $detail->save();
                }
            }
            Product::where('id', $product->id)->elastic()->addToIndex();
        }
        return $this->respondOk();
    }

    public function setToNotUpdateByCategory(Request $request)
    {
        if(!empty($request->category_id) && !empty($request->brand_id)){
            $products = Product::where('category_id', $request->category_id)
                ->where('brand_id', $request->brand_id)->get();
        } elseif(!empty($request->category_id)) {
            $products = Product::where('category_id', $request->category_id)->get();
        } else {
            $products = Product::where('brand_id', $request->brand_id)->get();
        }
        foreach ($products as $product) {
            foreach($product->product_details as $detail) {
                $detail->uid = null;
                $detail->save();
            }
            Product::where('id', $product->id)->elastic()->addToIndex();
        }
        return $this->respondOk();
    }

    public function setToNotExistByStore(Request $request)
    {
        $storeId = $request->store_id;
//        if($request->has('category_id')) {
//            $products = Product::where('store_id',$storeId)->where('category_id',$request->category_id)->get();
//        } elseif ($request->has('brand_id')) {
//            $products = Product::where('store_id',$storeId)->where('brand_id',$request->brand_id)->get();
//        } else {
        $products = ProductDetail::where('store_id', $storeId)->get();
//        }
        foreach ($products as $detail) {
            $detail->quantity = 0;
            $detail->save();
            Product::where('id', $detail->product_id)->elastic()->addToIndex();
        }

//        foreach($products as $product){
//            foreach($product->product_details as $detail) {
//                if($detail->store_id != $storeId) {
//                    $detail->uid = null;
//                    $detail->quantity = 3;
//                    $detail->save();
//                }
//            }
//            Product::where('id', $product->id)->elastic()->addToIndex();
//        }
        return $this->respondOk();
    }

    public function correctProductExistStatus()
    {
        $NEProducts = Product::where('quantity',0)->where('exist_status',Product::EXISTS);
        $NEProductIds = $NEProducts->get()->pluck('id')->toArray();
        $NEProducts->update(['exist_status'=>Product::NOT_EXISTS]);
        Product::whereIn('id',$NEProductIds)->elastic()->get()->addToIndex();
        $EProducts = Product::where('quantity','>',0)->where('exist_status',Product::NOT_EXISTS);
        $EProductIds = $EProducts->get()->pluck('id')->toArray();
        $EProducts->update(['exist_status'=>Product::EXISTS]);
        Product::whereIn('id',$EProductIds)->elastic()->get()->addToIndex();
        return $this->respondOk();
    }

    public function getRecommendationBaseOnProduct(Request $request)
    {
        $itemBaseRecommendation = new ItemBaseRecommendation();
        return $this->respondArray(
            $itemBaseRecommendation
                ->setClientId($request->client_id)
                ->setProductId($request->product_id)
                ->getProducts()
        );
    }

    public function correctNoDescriptions(Request $request)
    {
        $products = Product::where('description','like','%'."توضیحاتی برای این کالا ثبت نشده است".'%')->get()->pluck('id')->toArray();
        Product::whereIn('id',$products)->update(['description'=>""]);
        Product::whereIn('id',$products)->elastic()->get()->addToIndex();
    }

    public function transformProductsToNewStyle()
    {
        $offset = request('offset');
        if($offset == 0) {
            Warranty::firstOrCreate(['warranty_name' => PersianUtil::toStandardPersianString("گارانتی سلامت فیزیکی و اصالت کالا")]);
        }
        $products = Product::where('id','>',300*$offset)->where('id','<=',300*($offset+1))->get();
        foreach($products as $product){
            $colors = empty($product->colors->toArray()) ? null : $product->colors;
            if(!empty($product->warranty_name)) {
                $warranty = Warranty::firstOrCreate(['warranty_name' => PersianUtil::toStandardPersianString($product->warranty_name)]);
                $warrantyId = $warranty->id;
            } else{
                $warrantyId= 1;
            }
            if (empty($colors)) {
                ProductDetail::create(['product_id' => $product->id, 'store_id' => $product->store_id,
                    'color_id' => null, 'warranty_id'=>$warrantyId , 'current_price' => $product->current_price, 'quantity' => $product->quantity
                ]);
            } else {
                foreach($colors as $color){
                    ProductDetail::create(['product_id' => $product->id, 'store_id' => $product->store_id,
                        'color_id' => $color->id, 'warranty_id'=>$warrantyId , 'current_price' => $product->current_price, 'quantity' => $product->quantity,
                    ]);
                }
            }
            $product->store->categories()->sync([$product->category_id], false);
        }
        Product::where('id','>',300*$offset)->where('id','<=',300*($offset+1))->elastic()->addToIndex();
    }

    public function addToElastic()
    {
        $offset = request('offset');
        Product::where('id','>',300*$offset)->where('id','<=',300*($offset+1))->elastic()->addToIndex();
    }

    public function search()
    {
        $name = request()->name;
        if (empty($name)) {
            return null;
        }
        $nameQuery = '%' . $name . '%';
        $products = Product::where('english_name', 'like', $nameQuery)
            ->orWhere('persian_name', 'like', $nameQuery)->orWhere('id',$name)->select(['id', 'persian_name', 'english_name'])->take(50)->get();
        return $products;
    }
    public function deleteDuplicateProducts()
    {
        Product::where('id','>',37757)->where('id','<',37765)->delete();
        $client = ClientBuilder::create()->build();
        $param = [
            'type' => 'products', 'index' => 'wego_1',
            'body' => ['query' => ['filtered' => ['filter' => ['terms' => ['id' => [
                37764,37763,37762,37761,37760,37759,37758
            ]]]]]]
        ];
        $client->deleteByQuery($param);
        return $this->respondOk();
    }

    public function setProductPrice(Request $request)
    {
        $productId = $request->product_id;
        $price = $request->price;
        $product = Product::find($productId);
        $product->current_price = $price;
        $product->quantity = 5;
        $product->save();
        Product::where('id',$productId)->elastic()->get()->addToIndex();
        return $this->respondOk();
    }

    public function getSimilarProducts($id)
    {
        $product = Product::find($id);
        if (!empty($product)) {
            return ((new CategoryProducts($product->category_id))->get());
        }
        return $this->respondWithError('کالای درخواستی موجود نیست');
    }

    public function deliveryTime()
    {
        $wegobazaar = (new DeliveryTimeCalculator())->calculatePossibilities();
        $wegobazaar = $wegobazaar[0];
        $wegobazaar['time'] = $wegobazaar['time'][0];
        $time = explode('&',$wegobazaar['time']);
        $date = Shamsi::convert(Carbon::parse($time[0]));
        $hours = PersianUtil::to_persian_num($time[1]);
        $wegobazaar['time'] = $wegobazaar['day'].' مورخ '.$date.' ساعت '.$hours;
        $wegojet = (new DeliveryTimeCalculator())->calculateWegojetDeliveryTime();
        $wegojet = PersianUtil::to_persian_num($wegojet);
        $result = ['wegobazaar'=>$wegobazaar['time'] , 'wegojet'=>$wegojet];
        return $result;
    }

    public function nameCorrection()
    {
        $offset = request()->offset;
        $startId = 8378+(20*$offset);
        $endId = 8378+(20*($offset+1));
        $products = Product::where('id','>',$startId)->where('id','<=',$endId)->get();
        foreach ($products as $product) {
            $product->english_name = preg_replace('/- [A-Z] /','',$product->english_name);
            $product->english_name = preg_replace('/- [A-Z]/','',$product->english_name);
            $product->persian_name = preg_replace('/- [A-Z] /','',$product->persian_name);
            $product->persian_name = preg_replace('/- [A-Z]/','',$product->persian_name);
            $product->save();
            Product::where('id',$product->id)->elastic()->get()->addToIndex();
        }
        return $endId;
    }

    public function setJashnvareKeyName()
    {
        $keyName = request()->key_name ;
        $ids = request()->ids;
        $products = Product::whereIn('id',$ids)->get();
        foreach ($products as $product) {
            $product->key_name = $product->key_name . ' '.$keyName ;
            $product->save();
            Product::where('id',$product->id)->elastic()->addToIndex();
        }
        return $this->respondOk();
    }

    public function removeJashnvareKeyName()
    {
        $keyName = request()->key_name;
        $products = Product::where('key_name','like',"%".$keyName."%");
        foreach ($products as $product) {
            $product->key_name = str_replace($keyName,'',$product->key_name);
            $product->save();
            Product::where('id',$product->id)->elastic()->addToIndex();
        }
        return $this->respondOk();
    }

    public function removeJashnvareKeyNameById()
    {
        $keyName = request()->key_name;
        $ids= request()->ids;
        $products = Product::whereIn('id',$ids)->get();
        foreach ($products as $product) {
            $product->key_name = str_replace($keyName,'',$product->key_name);
            $product->save();
            Product::where('id',$product->id)->elastic()->addToIndex();
        }
        return $this->respondOk();
    }

    public function mergeProducts(Request $request)
    {
        $baseId = $request->baseId;
        $ids = $request->ids;
        ProductDetail::whereIn('product_id',$ids)->update(['product_id'=>$baseId]);
        $client = ClientBuilder::create()->build();
        $param = [
            'type' => 'products', 'index' => 'wego_1',
            'body' => ['query' => ['filtered' => ['filter' => ['terms' => ['id' => $ids]]]]]
        ];
        $client->deleteByQuery($param);
        Product::whereIn('id',$ids)->delete();
        Product::where('id',$baseId)->elastic()->addToIndex();
    }

    public function changeQuantities()
    {
        ProductDetail::where('quantity','>',0)->update(['quantity'=>100]);
    }

    public function addGiftsToLGProducts()
    {
        $products = Product::where('category_id',209)->where('brand_id',56)->get();
        foreach ($products as $product) {
            foreach ($product->product_details as $detail) {
                SpecialCondition::create(['type'=>'gift','product_detail_id'=>$detail->id,"text"=>"کارت هدیه نقدی جشنواره نوروزی 90 روز با ال جی"
                    ,"amount"=>0,"upper_value"=>1,"upper_value_type"=>"عدد","expiration"=>29]);
            }
            Product::where('id',$product->id)->elastic()->addToIndex();
        }
    }

    public function addGiftsToSamsungProducts()
    {
        $products = Product::where('category_id',209)->where('brand_id',55)->get();
        foreach ($products as $product) {
            foreach ($product->product_details as $detail) {
                if($detail->store_id == 92) {
                    SpecialCondition::create(['type' => 'gift', 'product_detail_id' => $detail->id, "text" => "کارت هدیه نقدی جشنواره عیدانه 1397 سام سرویس"
                        , "amount" => 0, "upper_value" => 1, "upper_value_type" => "عدد", "expiration" => 29]);
                }
            }
            Product::where('id',$product->id)->elastic()->addToIndex();
        }
    }

    public function festivalProductByCategory()
    {
        $key = request()->key_name;
        $query = new ElasticQueryMaker(['from'=>0,'key_name'=>$key],500);
        $client = ClientBuilder::create()->build();
        $product = $client->search($query->fillQuery()->getQuery());
        $product = $this->setDefaultPicture($product);
        $product = $this->setProductsUrl($product);
        $result = [];
        foreach ($product['hits']['hits'] as $prd) {
            if($key=='jashnvare97norouz'){
                if(strpos($prd['_source']['key_name'],'special97norouz') === false){
                    $result[$prd['_source']['category']['persian_name']][] = $prd['_source'];
                }
            } else {
                $result[$prd['_source']['category']['persian_name']][] = $prd['_source'];
            }
        }


        return $result;
    }

    private function setDefaultPicture($product)
    {
        foreach ($product['hits']['hits'] as &$prd) {
            usort($prd['_source']['pictures'], function ($a, $b) {
                return $a['type'] - $b['type'];
            });
            $prd['_source']['picture'] = empty($prd['_source']['pictures']) ? "/wego-logo.png" : $prd['_source']['pictures'][0]['path'];
            unset($prd['_source']['pictures']);
        }
        return $product;
    }

    private function setProductsUrl($product)
    {
        foreach ($product['hits']['hits'] as &$prd) {
            $prd['_source']['url'] = Product::url($prd['_source']['id'],$prd['_source']['english_name'],$prd['_source']['persian_name']);

        }
        return $product;
    }

    public function updateMahestan()
    {
        $details = ProductDetail::where('warranty_id',92)->get();
        foreach($details as $detail){
            Product::where('id',$detail->product_id)->elastic()->addToIndex();
        }
    }

    public function changeBooksPrice(Request $request)
    {
        $offset = $request->offset;
        $startId= (300 * $offset) + 10000;
        $endId = (300 * ($offset+1)) + 10000;
        dump($startId,$endId);
        $products = Product::where('id','>',$startId)->where('id','<=',$endId)->where('category_id',488)->get();
        foreach($products as $product){
            foreach ($product->product_details as $detail) {
                $amount = (.05 * $detail->current_price);
                SpecialCondition::create(['type'=>'discount','product_detail_id'=>$detail->id,"text"=>""
                    ,"amount"=>$amount,"upper_value"=>1,"upper_value_type"=>"عدد","expiration"=>90]);
//                SpecialCondition::where('product_detail_id',$detail->id)->where('status',SpecialCondition::AVAILABLE)
//                    ->delete();
            }
            Product::where('id',$product->id)->elastic()->addToIndex();
        }
        return $this->respondOk();
    }

    public function setDailyOfferStatus($id)
    {
        $product = Product::find($id);
        if(request()->status == 0){
            $product->key_name = str_replace(' dailyoffer','',$product->key_name);
            $product->key_name = str_replace('dailyoffer','',$product->key_name);
        } else {
            $hasOffer = $this->checkIfProductHasDiscount($product);
            if(!$hasOffer){
                return $this->respondWithError('این کالا تخفیف ندارد');
            }
            $product->key_name = $product->key_name . ' dailyoffer';
        }
        $product->save();
        Product::where('id',$product->id)->elastic()->addToIndex();
        return $this->respondOk();
    }

    public function setBooksUids(Request $request)
    {
        $offset = $request->offset;
        $startId= (300 * $offset) + 10000;
        $endId = (300 * ($offset+1)) + 10000;
        dump($startId,$endId);
        $products = Product::where('id','>',$startId)->where('id','<=',$endId)->where('category_id',488)->get();
        foreach($products as $product){
            $pic = $product->pictures->first();
            if(!empty($pic->path)) {
                $path = basename($pic->path);
                $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $path);
                $uid = explode('_', $withoutExt)[1];
                foreach ($product->product_details as $detail) {
                    $detail->uid = $uid;
                    $detail->Save();
                }
            }
        }
    }


    public function setUid(Request $request)
    {
        if(!empty($request->category_id) && !empty($request->brand_id)){
            $products = Product::where('category_id', $request->category_id)
                ->where('brand_id', $request->brand_id)->get();
        } elseif(!empty($request->category_id)) {
            $products = Product::where('category_id', $request->category_id)->get();
        } else {
            $products = Product::where('brand_id', $request->brand_id)->get();
        }
        foreach($products as $product){
            $pic = $product->pictures->first();
            if(!empty($pic->path)) {
                $path = basename($pic->path);
                $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $path);
                $uid = explode('_', $withoutExt)[1];
                if(is_numeric($uid) && strlen($uid)<10) {
                    foreach ($product->product_details as $detail) {
                        $detail->uid = $uid;
                        $detail->Save();
                    }
                }
            }
        }
    }

    /**
     * @param $product
     * @return bool
     */
    protected function checkIfProductHasDiscount($product)
    {
        $hasOffer = false;
        foreach ($product->product_details as $detail) {
            $offer = $detail->special_conditions->where('status', SpecialCondition::AVAILABLE)
                ->where('type','discount')->toArray();
            if (!empty($offer)) {
                $hasOffer = true;
                break;
            }
        }
        return $hasOffer;
    }
    public static function setNotUpdateToNotExist()
    {
        $productDetails = ProductDetail::where('updated_at','<',Carbon::now()->subHours(12))
            ->whereNotNull('uid')->where('uid','<>',0)->where('quantity','>',0)->get();
        foreach ($productDetails as $detail){
            if($detail->product->category_id != ExcelController::BOOK){
                $detail->quantity = 0;
                $detail->save();
                Product::where('id',$detail->product_id)->elastic()->addToIndex();
            }
        }
    }

    public function checkOldNotExistingOrders()
    {
        set_time_limit(60000);
        $orders = OutsideOrder::all()->pluck('name')->toArray();
        $products = Product::whereIn('persian_name',$orders)->get();
        $count = 0;
        foreach($products as $product){
            $detail = ProductDetail::where('product_id',$product->id)->where('quantity','>',0)->first();
            if(!empty($detail)){
                $count++;
                if($count<150){
                    continue;
                }
                $phone_numbers = OutsideOrder::where('name',$product->persian_name)->get()->pluck('phone_number')->toArray();
                $client = new KavenegarApi(env('SMS_API_KEY'));
                $originalUrl = 'http://shiii.ir/product/'.$product->id;
                $url = $this->shorten($originalUrl);
                foreach(array_chunk($phone_numbers,190) as $chunkNumbers){
                    try {
                        $client->Send(env('SMS_NUMBER'), $chunkNumbers,
                            "کاربر گرامی کالای درخواستی شما موجود شد. همین حالا می توانید از طریق لینک زیر کالای خود را خریداری نمایید.\n"
                            . $url . "\n" . "ویگوبازار"
                        );
                    } catch(\Exception $ex){
                        continue;
                    }
                }
            }
        }
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
}
