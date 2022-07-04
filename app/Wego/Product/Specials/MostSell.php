<?php
namespace Wego\Product\Specials;

use Illuminate\Support\Facades\Lang;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 07/09/17
 * Time: 15:32
 */
class MostSell extends  AbstractSpecial
{

    public function getProducts()
    {
        return  $this->productElasticSearch->mostSell();
    }

    public function setTitle()
    {
        $this->title = Lang::get('ProductGroupMessage.best_seller_product');
        return $this;
    }
}