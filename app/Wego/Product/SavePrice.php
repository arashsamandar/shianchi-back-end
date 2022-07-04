<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 2/10/16
 * Time: 9:03 AM
 */

namespace Wego\Product;


use App\Product;

class SavePrice
{
    public function save(Product $product, $price)
    {
        $product->prices()->create(["price" => $price]);
    }
}