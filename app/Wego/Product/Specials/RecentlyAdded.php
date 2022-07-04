<?php
namespace Wego\Product\Specials;

use Illuminate\Support\Facades\Lang;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 07/09/17
 * Time: 15:32
 */
class RecentlyAdded extends AbstractSpecial
{

    public function getProducts()
    {
        return $this->productElasticSearch->recentlyAdded();
    }

    public function setTitle()
    {
        $this->title = Lang::get('ProductGroupMessage.recent_product');
        return $this;
    }
}