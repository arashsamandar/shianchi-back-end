<?php
namespace Wego\Product\Specials;

use Illuminate\Support\Facades\Lang;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 07/09/17
 * Time: 15:32
 */
class DailyOffer extends AbstractSpecial
{

    public function getProducts()
    {
        return  $this->productElasticSearch->dailyOffer();
    }

    public function setTitle()
    {
        $this->title = Lang::get('ProductGroupMessage.daily_offer_product');
        return $this;
    }
}