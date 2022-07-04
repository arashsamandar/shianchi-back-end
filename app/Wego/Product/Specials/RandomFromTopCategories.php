<?php
namespace Wego\Product\Specials;

use Illuminate\Support\Facades\Lang;
use Wego\Helpers\TopCategories;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 07/09/17
 * Time: 16:46
 */
class RandomFromTopCategories extends AbstractSpecial
{

    public function getProducts()
    {
        $randomCategory = $this->getRandomFromTopCategories();
        $this->appendTitle('ی '.$randomCategory->persian_name);
        return $this->productElasticSearch->withCategory($randomCategory->name);
    }

    /**
     * @return array
     */
    private function getRandomFromTopCategories()
    {
        return  (new TopCategories)->getRandomName();
    }

    public function setTitle()
    {
        $this->title = Lang::get('ProductGroupMessage.most_viewed_product');
        return $this;
    }
}