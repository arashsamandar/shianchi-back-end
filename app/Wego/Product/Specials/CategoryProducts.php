<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 11/14/17
 * Time: 3:03 PM
 */

namespace Wego\Product\Specials;


use App\Product;
use Elasticsearch\ClientBuilder;

class CategoryProducts extends AbstractSpecial
{
    private $categoryId;
    public function __construct($categoryId)
    {
        parent::__construct();
        $this->categoryId = $categoryId;
    }

    public function getProducts()
    {
        return $this->productElasticSearch->withCategoryId($this->categoryId);
    }

    public function setTitle()
    {
        $this->title = 'کالاهای مشابه';
        return $this;
    }
}