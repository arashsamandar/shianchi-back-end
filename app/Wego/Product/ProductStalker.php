<?php
/**
 * Created by PhpStorm.
 * User: wb-2
 * Date: 7/4/16
 * Time: 12:56 PM
 */

namespace Wego\Product;


class ProductStalker
{
    protected $product;
    const PRICE_CHANGED = 0;
    const SPECIAL_CREATED = 1;
    const SPECIAL_UPDATED = 2;
    const SPECIAL_DELETED = 3;

    /**
     * ProductStalker constructor.
     * @param $product
     */
    public function __construct($product)
    {
        $this->product = $product;
    }


}
