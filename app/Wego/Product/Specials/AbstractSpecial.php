<?php
namespace Wego\Product\Specials;

use App\Product;
use Wego\Helpers\JsonUtil;
use Wego\Search\ProductElasticSearch;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 07/09/17
 * Time: 15:37
 */
abstract class AbstractSpecial
{
    public $productElasticSearch, $title, $products;

    abstract public function getProducts();
    abstract public function setTitle();

    function __construct()
    {
        $this->productElasticSearch = new ProductElasticSearch();
    }

    public function get()
    {
        return $this->format($this->setTitle()->getProducts());
    }

    public function format($products)
    {
        return [$this->removeFields($products), 'title' => $this->title];
    }

    private function removeFields($products)
    {
        return array_map([$this, 'prettifyEachProduct'], JsonUtil::removeFields($products['hits']['hits'], [
            '*._index', '*._type', '*._id', '*._score', '*._source.pictures.*.id', '*._source.pictures.*.updated_at',
            '*._source.pictures.*.created_at', '*._source.pictures.*.product_id'
        ]));
    }

    public function prettifyEachProduct($products)
    {
        foreach ($products as $product) {
            usort($product['pictures'], function ($a, $b) {
                return $a['type'] - $b['type'];
            });
            return [
                'persian_name' => $product['persian_name'],
                'english_name' => $product['english_name'],
                'current_price' => $product['current_price'],
                'id' => $product['id'],
                'path' => empty($product['pictures']) ? '/wego-logo.png' : $product['pictures'][0]['path'],
                'special_conditions' => $product['special_conditions'],
                'url' => Product::url($product['id'], $product['english_name'], $product['persian_name'])
            ];
        }

    }

    public function appendTitle($text)
    {
        $this->title = $this->title . $text;
    }

}
