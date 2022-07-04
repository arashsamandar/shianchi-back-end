<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 12/18/16
 * Time: 5:06 PM
 */

namespace Wego;


use App\Product;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;

class ProductViewCount
{
    /**
     *
     */
    public static function setUpProductViewCount()
    {
        $client = ClientBuilder::create()->build();
        $products = Product::all(['id']);
        foreach ($products as $product) {
            $params = [
                'index' => 'wego' . Carbon::now()->toDateString(),
                'type' => 'productViewCount',
                'id' => $product->id,
                'body' => [
                    'product_id' => $product->id
                ],
            ];
            $client->index($params);
        }
    }

    /**
     * @param $productId
     * @return array
     */
    public static function findProductViewCountByProductId($productId)
    {
        $params = [
            'index' => 'wego' . Carbon::now()->toDateString(),
            'type' => 'productViewCount',
            'body' => [
                'query' => [
                    'match' => [
                        'product_id' => $productId
                    ]
                ]
            ],
            'size' => 1
        ];
        $client = ClientBuilder::create()->build();
        return $client->search($params);
    }

    /**
     * @param $ip
     * @param $productId
     */
    public static function addIpToProductViewIps($ip, $productId)
    {
        if (self::indexExists())
            self::addIpToElastic($ip, $productId);
    }

    /**
     * @param $result
     * @param $productId
     * @param $ip
     * @return array
     */
    public static function updateProductViewCount($result, $productId, $ip)
    {
        $client = ClientBuilder::create()->build();
        $result['hits']['hits'][0]['_source']['IPs'][] = $ip;
        $params = [
            'index' => 'wego' . Carbon::now()->toDateString(),
            'type' => 'productViewCount',
            'id' => $productId,
            'body' => [
                'doc' => $result['hits']['hits'][0]['_source']
            ]
        ];
        try {
            return $client->update($params);
        } catch (\Exception $e){
            self::addIpToElastic($ip,$productId);
        }
    }

    /**
     * @param $productId
     * @param $ip
     */
    public static function insertProductViewCount($productId, $ip)
    {
        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'wego' . Carbon::now()->toDateString(),
            'type' => 'productViewCount',
            'id' => $productId,
            'body' => [
                'product_id' => $productId
            ],
        ];
        $params['body']['IPs'][] = $ip;
        $client->index($params);
    }

    /**
     * @param $result
     * @return bool
     */
    public static function documentNotExists($result)
    {
        return ($result['hits']['hits'] == null);
    }

    /**
     * @param $params
     */
    private static function indexExists()
    {
        $client = ClientBuilder::create()->build();
        $params['index'] = 'wego' . Carbon::now()->toDateString();
        return $client->indices()->exists($params);
    }

    /**
     * @param $ip
     * @param $productId
     */
    private static function addIpToElastic($ip, $productId)
    {
        $result = self::findProductViewCountByProductId($productId);
        if (self::documentNotExists($result))
            self::insertProductViewCount($productId, $ip);
        else
            self::updateProductViewCount($result, $productId, $ip);
    }

    /**
     * @return array
     */
    public function showAll()
    {
        $params = [
            'index' => 'wego' . Carbon::now()->toDateString(),
            'type' => 'productViewCount',
            'body' => [
                'query' => [
                    'match_all' => []
                ]
            ],
            'size' => 100
        ];
        $client = ClientBuilder::create()->build();
        $result = $client->search($params);
        return $result;
    }

    /**
     * @param $productId
     * @return mixed
     */
    public static function countVisitors($productId)
    {
        $params = [
            'index' => 'wego' . Carbon::now()->subDay(1)->toDateString(),
            'type' => 'productViewCount',
            "size" => 0,
            "body" => ["aggs" => [
                "filtered_entities" => [
                    "filter" => [
                        "bool" => [
                            "must" => [
                                "term" => ["product_id" => $productId]
                            ]
                        ]
                    ],
                    "aggs" => [
                        "distinct_ips" => [
                            "cardinality" => [
                                "field" => "IPs",
                                "precision_threshold" => 100
                            ]
                        ]
                    ]
                ]
            ]]
        ];
        $client = ClientBuilder::create()->build();
        $result = $client->search($params);
        return $result['aggregations']['filtered_entities']['distinct_ips']['value'];
    }

    /**
     *
     */
    public static function calculateAllProductsVisitorCount()
    {
        $products = Product::where('confirmation_status', Product::CONFIRMED)->get();
        foreach ($products as $product) {
            $count[$product->id] = self::countVisitors($product->id);
            self::addCountToElastic($product->id, $count[$product->id]);
        }
    }

    /**
     *
     */
    public function deleteAll()
    {
        $params = [
            'index' => 'wego' . Carbon::now()->toDateString(),
            'type' => 'productViewCount',
            'body' => [
                'query' => [
                    'match_all' => []
                ]
            ],
        ];
        $client = ClientBuilder::create()->build();
        $result = $client->deleteByQuery($params);
    }

    /**
     * @param $productId
     * @param $count
     */
    public static function addCountToElastic($productId, $count)
    {
        $result = Product::find($productId);
        $days = Carbon::now()->diffInDays(Carbon::parse($result->created_at)->setTime(0, 0));
        $result->timestamps = false;
        $result->view_count = (($result->view_count * ($days - 1)) + $count) / $days;
        $result->save();
        $result->timestamps = true;
        $result->save();
        Product::where('id',$productId)->elastic()->addToIndex();
    }
}