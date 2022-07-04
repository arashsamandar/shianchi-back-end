<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 07/09/17
 * Time: 15:30
 */

namespace Wego\Product;


use Wego\Search\ProductElasticSearch;

class ProductGroup
{
    public function getProductGroup(Request $request)
    {
        $type = $request->input('type');
        $cat = $this->getCategoryResponse($type);
        if ($this->recentlyAddedProductsRequested($type)) {
            $products = (new ProductElasticSearch())->recentlyAdded();
        } elseif ($this->mostviewedProductRequested($type)) {
            $products = (new ProductElasticSearch())->mostViewed();
        } elseif ($this->bestSellerProductsRequested($type)) {
            $products = (new ProductElasticSearch())->mostSell();
        } elseif ($this->clientIdSentInRequest($request)) {
            try {
                $products = $this->getProductsFromRecommender($request);
                $cat['title'] = 'پیشنهاد ویگوبازار به شما';
                if ($products['hits']['total'] == 0) {
                    $randomCategory = $this->getRandomFromTopCategories();
                    $products = (new ProductElasticSearch())->withCategory($randomCategory->name);
                    $cat['title'] = $cat['title'] . $randomCategory->persian_name;
                }
            } catch (\Exception $e) {
                $randomCategory = $this->getRandomFromTopCategories();
                $products = (new ProductElasticSearch())->withCategory($randomCategory->name);
                $cat['title'] = $cat['title'] . $randomCategory->persian_name;
            }
        } else {
            $randomCategory = $this->getRandomFromTopCategories();
            $products = (new ProductElasticSearch())->withCategory($randomCategory->name);
            $cat['title'] = $cat['title'] . $randomCategory->persian_name;
        }
        return $this->respondArray([$this->prettifyProductGroup($products), 'title' => $cat['title']]);
    }

}