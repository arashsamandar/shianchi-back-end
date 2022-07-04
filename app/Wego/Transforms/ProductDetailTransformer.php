<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 02/10/17
 * Time: 12:29
 */

namespace Wego\Transforms;

use App\ProductDetail;
use League\Fractal\TransformerAbstract;

class ProductDetailTransformer extends TransformerAbstract
{
    use TransformerHelper;

    protected $fields;

    function __construct($fields = null)
    {
        $this->fields = $fields;
    }

    protected $availableIncludes = ['store'];

    protected $defaultIncludes = ['store'];

    public function transform(ProductDetail $productDetail)
    {
        return [
            "current_price" => ($productDetail->pivot->price + $productDetail->pivot->discount) / $productDetail->pivot->quantity,
            "quantity" => $productDetail->pivot->quantity,
            "total_price" => $productDetail->pivot->price,
            "discount" => $productDetail->pivot->discount,
            "gift" => $productDetail->pivot->gift,
            "gift_count" => $productDetail->pivot->gift_count,
            "english_name" => $productDetail->product->english_name,
            "persian_name" => $productDetail->product->persian_name,
            "product_id" => $productDetail->product->id,
            "detail_id" => $productDetail->id,
            "color" => $productDetail->color,
            "warranty" => $productDetail->warranty
        ];
    }

    public function includeStore(ProductDetail $productDetail)
    {
        $store = $productDetail->store;
        return $this->item($store, new StoreTransformer('persian_name,english_name,address'));
    }
}