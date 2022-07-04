<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\Repositories\ProductPictureRepository;
use App\Repositories\StorePictureRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Wego\ElasticHelper;
use Wego\Helpers\JsonUtil;
use Wego\Product\ProductParser;
use Wego\Search\ElasticKeywordQueryBuilder;
use Elasticsearch\ClientBuilder;
use Wego\Search\ElasticQueryMaker;

class SearchController extends ApiController
{
    protected $productPictureRepository, $storePictureRepository;

    function __construct(ProductPictureRepository $productPictureRepository, StorePictureRepository $storePictureRepository)
    {
        $this->productPictureRepository = $productPictureRepository;
        $this->storePictureRepository = $storePictureRepository;
    }

    public function index(Request $request)
    {
        $query = new ElasticQueryMaker($request->all());
        $client = ClientBuilder::create()->build();
        try {
            $product = $client->search($query->fillQuery()->getQuery());
        } catch (\Exception $e){
            Log::info($request->ip());
            Log::info($request->all());
            dd($e->getMessage());
        }
        $product = $this->setDefaultPicture($product);
        $product = $this->setProductsUrl($product);
        $product = ElasticHelper::paginate($product,$request->input('from'),ElasticQueryMaker::SIZE);
        $product['category'] = isset($request['category']) ? $this->getCategoryPath($request['category']) : null;
        return $product;
    }

    public function keyword(Request $request)
    {
        $query = new ElasticKeywordQueryBuilder($request);
        $client = ClientBuilder::create()->build();

        $queryRun = $query->run();
        $result = $client->search($queryRun);

        $array = (collect($result['hits']['hits'])->groupBy('_type')->toArray());
        if (isset($array['categories'])) {
            $array = $this->getOnlyLeafCategories($array);
        }

        return $this->prepareResponse($array);
    }

    private function prepareResponse($param)
    {
        $param = $this->prepareCategoryResponse($param);
        $param = $this->prepareProductResponse($param);
        //$param = $this->prepareStoreResponse($param);
        return $param;
    }

    /**
     * @param $param
     * @return mixed
     */
    private function setCategoryIfNotSet($param)
    {
        if (!isset($param["categories"]))
            $param["categories"] = [];
        return $param;
    }

    /**
     * @param $param
     * @return array
     */
    private function prepareProductResponse($param)
    {
        $param = $this->setProductsIfNotSet($param);
        $param['products'] = array_slice($param['products'],0,10);
        $param = $this->prepareProductResponseIfSet($param);
        return $param;
    }

    private function prepareCategoryResponse($param)
    {
        $param = $this->setCategoryIfNotSet($param);
        $param['categories'] = array_slice($param['categories'],0,3);
        $param = $this->prepareCategoryResponseIfSet($param);
        return $param;
    }

    /**
     * @param $param
     * @return mixed
     */
    private function setProductsIfNotSet($param)
    {
        if (!isset($param["products"]))
            $param["products"] = [];
        return $param;
    }

    /**
     * @param $param
     * @return mixed
     */
    private function prepareProductResponseIfSet($param)
    {
        if (isset($param["products"])) {
            foreach ($param['products'] as &$product) {
                $picture = ['path' => $this->getMainPictureOfProduct($product['_source']['pictures'])];
                $product['_source'] = JsonUtil::convertKeys($product['_source'], ['picture' => 'pictures']);
                $product['_source']['picture'] = $picture;
                $product['_source']['url'] = Product::url($product['_id'],$product['_source']['english_name'],$product['_source']['persian_name']);
            }
        }
        return $param;
    }

    /**
     * @param $param
     * @return mixed
     */
    private function prepareStoreResponse($param)
    {
        $param = $this->setStoreIfNotSet($param);
        $param = $this->prepareStoreResponseIfSet($param);
        return $param;
    }

    /**
     * @param $param
     * @return mixed
     */
    private function setStoreIfNotSet($param)
    {
        if (!isset($param["stores"]))
            $param["stores"] = [];
        return $param;
    }

    /**
     * @param $param
     * @return mixed
     */
    private function prepareStoreResponseIfSet($param)
    {
        if (isset($param["stores"])) {
            foreach ($param['stores'] as &$store) {
                $picture = ['path' => $this->getLogoOfStore($store['_source']['pictures'])];
                $persian_name = $store['_source']['user']['name'];
                unset($store['_source']['user']);
                $store['_source'] = JsonUtil::convertKeys($store['_source'], ['picture' => 'pictures']);
                $store['_source']['picture'] = $picture;
                $store['_source']['persian_name'] = $persian_name;
            }
        }
        return $param;
    }

    private function prepareCategoryResponseIfSet($param)
    {
        foreach ($param['categories'] as &$category) {
            $product['_source'] = JsonUtil::convertKeys($category['_source'], ['path' => 'persian_name']);
        }
        return $param;
    }

    /**
     * @param $pictures
     * @return mixed
     */
    private function getMainPictureOfProduct($pictures)
    {
        foreach ($pictures as $picture) {
            $path = $this->productPictureRepository->firstWhere(['path' => $picture['path'], 'type' => '0']);
            if (isset($path)) {
                return $path->path;
            }
        }
    }

    /**
     * @param $pictures
     * @return mixed
     */
    private function getLogoOfStore($pictures)
    {
        foreach ($pictures as $picture) {
            $path = $this->storePictureRepository->firstWhere(['path' => $picture['path'], 'type' => 'logo']);
            if (isset($path)) {
                return $path->path;
            }
        }
    }

    /**
     * @param $array
     * @return mixed
     */
    private function getOnlyLeafCategories($array)
    {
        foreach ($array['categories'] as $key => $category) {
            if ($category['_source']['isLeaf'] == 0) {
                unset($array['categories'][$key]);
            }
        }
        $array['categories'] = array_values($array['categories']);
        return $array;
    }

    private function getCategoryPath($category)
    {
        $cat = Category::where('name',$category)->first();
        if (!is_null($cat)){
            return (new ProductParser([]))->prepareBreadcrumb($cat->english_path,$cat->path);
        }
        return null;
    }

    /**
     * @param $product
     * @param $prd
     * @return mixed
     */
    private function setDefaultPicture($product)
    {
        foreach ($product['hits']['hits'] as &$prd) {
            usort($prd['_source']['pictures'], function ($a, $b) {
                return $a['type'] - $b['type'];
            });
            $prd['_source']['picture'] = empty($prd['_source']['pictures']) ? "/wego-logo.png" : $prd['_source']['pictures'][0]['path'];
            unset($prd['_source']['pictures']);
        }
        return $product;
    }

    private function setProductsUrl($product)
    {
        foreach ($product['hits']['hits'] as &$prd) {
            $prd['_source']['url'] = Product::url($prd['_source']['id'],$prd['_source']['english_name'],$prd['_source']['persian_name']);

        }
        return $product;
    }
}
