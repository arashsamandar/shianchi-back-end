<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 8/13/18
 * Time: 5:28 PM
 */

namespace Wego\Transforms;


use App\OrderProduct;
use App\ProductDetail;
use App\ProductPicture;
use App\StoreOffer;
use League\Fractal\TransformerAbstract;

class OrderOfferDetails extends TransformerAbstract
{
    use TransformerHelper;
    protected $storeId ;
//    protected $validParams = ['status'];
//    protected $availableIncludes = [
//        'address',
//        'products'
//    ];
//
//    protected $defaultIncludes = [
//        'address',
//        'products'
//    ];

    function __construct($storeId)
    {
        $this->storeId = $storeId;
    }
    public function transform(OrderProduct $orderProduct)
    {
        $detailId = $orderProduct->detail_id;
        $productDetail= ProductDetail::find($detailId);
        $bestPrice = StoreOffer::where('order_product_id',$orderProduct->id)
            ->orderBy('store_price','desc')->first();
        $img = ProductPicture::where('product_id',$productDetail->product_id)
            ->where('type',0)->first();
        $storePrice = StoreOffer::where('order_product_id',$orderProduct->id)
            ->orderBy('store_id',$this->storeId)->first();

        $offers = StoreOffer::where('order_product_id',$orderProduct->id)
            ->orderBy('store_price','asc')->with(['store'=>function($query){
                $query->with(['user'=>function($query){
                    $query->select(['userable_id','name']);
                }])->select('id');
            }])->get()->toArray();;


        return $this->transformWithFieldFilter([
            'order_product_id' => $orderProduct->id,
            'persian_name' => $productDetail->product->persian_name,
            'warranty_name' => (!empty($productDetail->warranty_id)) ? $productDetail->warranty->warranty_name : null,
            'color_name' => (!empty($productDetail->color)) ? $productDetail->color->persian_name : null,
            'img' => (!empty($img)) ? $img->path : null,
            'best_price' => (!empty($bestPrice)) ? $bestPrice->store_price : null,
            'quantity' => $orderProduct->quantity,
            'product_id' => $productDetail->product_id,
            'store_price' => (!empty($storePrice)) ? $storePrice->store_price : null,
            'offers' =>  $offers
        ],null);
    }

}