<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 10/09/17
 * Time: 14:51
 */

namespace Wego\Transforms;

use App\Product;
use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract
{
    protected $fields;
    use TransformerHelper;

    function __construct($fields = null)
    {
        $this->fields = $fields;
    }

    protected $availableIncludes = [
        'store',
        'category',
        'pictures',
        'special_conditions',
        'comments',
        'values',
        'colors',
        'brand'
    ];


    public function transform(Product $product)
    {
        return $this->transformWithFieldFilter(
            [
                "id" => $product->id,
                "english_name" => $product->english_name,
                "deleted_at" => $product->deleted_at,
                "persian_name" => $product->persian_name,
                "key_name" => $product->key_name,
                "description" => $product->description,
                "confirmation_status" => $product->confirmation_status,
                "weight" => $product->weight,
                "wego_coin_need" => $product->wego_coin_need,
                "length" => $product->length,
                "width" => $product->width,
                "height" => $product->height,
                "quantity" => $product->quantity,
                "warranty_name" => $product->warranty_name,
                "warranty_text" => $product->warranty_text,
                "store_id" => $product->store_id,
                "category_id" => $product->category_id,
                "current_price" => $product->current_price,
                "view_count" => $product->view_count,
                "brand_id" => $product->brand_id,
                "created_at" => $product->created_at,
                "updated_at" => $product->updated_at,
                "exist_status" => $product->exist_status,
                "sale" => $product->sale,
                "average_score" => $product->average_score
            ], $this->fields
        );
    }

    public function includeStore(Product $product)
    {
        $store = $product->store;
        return $this->item($store, new StoreTransformer($this->getFields('store.', $this->fields)));
    }

    public function includeCategory(Product $product)
    {
        $category = $product->category;
        return $this->item($category, new CategoryTransformer($this->getFields('category.', $this->fields)));
    }

    public function includePictures(Product $product)
    {
        $pictures = $product->pictures;

        return $this->collection($pictures, new PictureTransformer($this->getFields('pictures.',$this->fields)));
    }

    public function includeSpecialConditions(Product $product)
    {
        $specialCondition = $product->special_conditions;

        return $this->collection($specialCondition, new SpecialConditionTransformer($this->getFields('special_conditions.',$this->fields)));
    }

    public function includeComments(Product $product)
    {
        $comments = $product->comments;

        return $this->collection($comments, new CommentTransformer($this->getFields('comments.',$this->fields)));
    }

    public function includeValues(Product $product)
    {
        $values = $product->values;

        return $this->collection($values, new ValueTransformer($this->getFields('values.',$this->fields)));
    }

    public function includeColors(Product $product)
    {
        $colors = $product->colors;

        return $this->collection($colors, new ColorTransformer($this->getFields('colors.',$this->fields)));
    }

    public function includeBrand(Product $product)
    {
        $brand = $product->brand;

        return $this->item($brand, new BrandTransformer($this->getFields('brand.',$this->fields)));
    }
}