<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 2/25/16
 * Time: 2:44 PM
 */

namespace Wego\Cart;

use App\Color;
use App\Product;
use App\SpecialCondition;
use App\Value;
use App\Warranty;
use Elasticsearch\ClientBuilder;

class GenerateCart
{
    protected $finalResult = [];
    protected $productId, $request;
    protected $coinAvailable;
    protected $user;
    protected $productDetails;

    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    public function setDetails($details)
    {
        $this->productDetails = $details;
        return $this;
    }

    public function handle()
    {
        $client = ClientBuilder::create()->build();
        $productDetail = $client->search($this->getQuery());
        foreach ($this->productDetails as $detail) {
            foreach ($productDetail['hits']['hits'] as $item) {
                if ($item["_source"]["id"] == $detail->product_id) {
                    $this->finalResult[] = $this->productTransformer($item, $detail);
                }
            }
        }
        return $this;

    }

    public function getFinalResult()
    {
        return $this->finalResult;
    }


    public function saveCart()
    {
        $client = ClientBuilder::create()->build();
        $client->index($this->insertQuery());
        return $this;
    }

    private function insertQuery()
    {
        return [
            'index' => 'wego_1',
            'type' => 'carts',
            'id' => $this->user->id,
            'body' => ["information" => $this->getFinalResult()]
        ];
    }

    private function productTransformer($item, $detail)
    {
        $specials = $detail->special_conditions->where('status', SpecialCondition::AVAILABLE)->toArray();
        $specials = array_values($specials);
        return
            [
                "english_name" => $item["_source"]["english_name"],
                "product_id" => $item["_source"]["id"],
                "persian_name" => $item["_source"]["persian_name"],
                "key_name" => $item["_source"]["key_name"],
                "wego_coin_need" => $item["_source"]["wego_coin_need"],
                "price" => $detail->current_price,
                "quantity" => $detail->quantity,
                "warranty_name" => (!empty($detail->warranty_id)) ? $detail->warranty->warranty_name : null,
                "warranty_text" => (!empty($detail->warranty_id)) ? $detail->warranty->warranty_text : null,
                "special_condition" => $specials,
                "pictures" => $item["_source"]["pictures"][0],
                "values" =>  $detail->value ,
                "colors" => $detail->color ,
                "store"=>$detail->store->user->name,
                "url" => Product::url($item['_source']['id'], $item["_source"]["english_name"], $item["_source"]["persian_name"]),
                "detail_id" => $detail->id
            ];
    }

    private function getQuery()
    {

        return
            [
                'index' => 'wego_1',
                'type' => 'products',
                'body' =>
                    [
                        "query" => [
                            'filtered' => ['filter' => ['terms' => ['id' => $this->productId]]]
                        ]

                    ]
            ];
    }

}




