<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 9/26/17
 * Time: 11:57 AM
 */

namespace Wego\Shipping;


use App\Http\Controllers\ApiController;
use App\Product;
use App\ProductDetail;

class RequestedProducts
{
    protected $totalPrice;
    protected $products;

    public function __construct($quantities)
    {
        $this->products = ProductDetail::orderDetail(array_column($quantities, 'detail_id'))->mergeQuantity($quantities);
        foreach($this->products as $product){
           if($product->get('quantity') > $product->get('existing_quantity')){
               return (new ApiController())->setStatusCode(404)
                   ->respondWithError("متاسفانه کالای مورد نظر شما تمام شد.");
           }
            unset($product['existing_quantity']);
        };
    }

    public function getTotalPrice()
    {
        $totalPricePerProduct = $this->products->map(function ($product) {
            $discount = $product->get('special_conditions')->reject(function ($item) {
                return $item['type'] != 'discount';
            })->first();
            $discountAmount = empty($discount) ? 0 : floor($product->get('quantity') / $discount['upper_value']) * $discount['amount'];
            return ($product->get('quantity') * $product->get('current_price') - $discountAmount);
        });
        return $totalPricePerProduct->sum();
    }

    public function getTotalWeight()
    {
        $totalPricePerProduct = $this->products->map(function ($product) {
            return ($product->get('quantity') * $product->get('product')->get('weight'));
        });
        return $totalPricePerProduct->sum();
    }

    public function getOrderProductContent()
    {

        return $this->products->map(function ($product) {
            $discount = $product->get('special_conditions')->reject(function ($item) {
                return $item['type'] != 'discount';
            })->first();

            $gifts = $product->get('special_conditions')->reject(function ($item) {
                return $item['type'] != 'gift';
            })->first();

            $discountAmount = empty($discount) ? 0 : floor($product->get('quantity') / $discount['upper_value']) * $discount['amount'];
            $giftCount = empty($gifts) ? 0 : floor($product->get('quantity') / $gifts['upper_value']);

            return $product->merge(
                [
                    'status'=> Product::PRE_PURCHASE,
                    'discount' => $discountAmount,
                    'gift_count' => $giftCount,
                    'gift' => empty($gifts) ? "" : $gifts['text'],
                    'price' => $product->get('current_price') * $product->get('quantity') - $discountAmount
                ]
            )->forget('special_conditions')->forget('product')->forget('product_id')->forget('id')->forget('current_price');
        });
    }

}