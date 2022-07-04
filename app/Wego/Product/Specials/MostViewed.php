<?php
namespace Wego\Product\Specials;

use Illuminate\Support\Facades\Lang;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 07/09/17
 * Time: 15:36
 */
class MostViewed extends AbstractSpecial
{

    public function getProducts()
    {
        return $this->productElasticSearch->mostViewed();
    }

    public function setTitle()
    {
        $this->title = Lang::get('ProductGroupMessage.most_viewed_product');
        return $this;
    }
}