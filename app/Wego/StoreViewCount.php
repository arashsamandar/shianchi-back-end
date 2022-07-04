<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 12/18/16
 * Time: 5:05 PM
 */

namespace Wego;


use App\Store;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;

class StoreViewCount
{
    /**
     *
     */
    public static function setUpStoreViewCount()
    {
        $client = ClientBuilder::create()->build();
        $stores = Store::all(['id']);
        foreach ($stores as $store) {
            $params = [
                'index' => 'wego' . Carbon::now()->toDateString(),
                'type' => 'storeViewCount',
                'id' => $store->id,
                'body' => [
                    'store_id' => $store->id
                ],
            ];
            $client->index($params);
        }
    }

    /**
     * @param $storeId
     * @return array
     */
    public static function findStoreViewCountByStoreId($storeId)
    {
        $params = [
            'index' => 'wego' . Carbon::now()->toDateString(),
            'type' => 'storeViewCount',
            'body' => [
                'query' => [
                    'match' => [
                        'store_id' => $storeId
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
     * @param $storeId
     */
    public static function addIpToStoreViewIps($ip, $storeId)
    {
        if (self::indexExists())
            self::addIpToElastic($ip, $storeId);
    }

    /**
     * @param $result
     * @param $storeId
     * @param $ip
     * @return array
     */
    public static function updateStoreViewCount($result, $storeId, $ip)
    {
        $client = ClientBuilder::create()->build();
        $result['hits']['hits'][0]['_source']['IPs'][] = $ip;
        $params = [
            'index' => 'wego' . Carbon::now()->toDateString(),
            'type' => 'storeViewCount',
            'id' => $storeId,
            'body' => [
                'doc' => $result['hits']['hits'][0]['_source']
            ]
        ];
        return $client->update($params);
    }

    /**
     * @param $storeId
     * @param $ip
     */
    public static function insertStoreViewCount($storeId, $ip)
    {
        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'wego' . Carbon::now()->toDateString(),
            'type' => 'storeViewCount',
            'id' => $storeId,
            'body' => [
                'store_id' => $storeId
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
     * @param $ip
     * @param $storeId
     */
    private static function addIpToElastic($ip, $storeId)
    {
        $result = self::findStoreViewCountByStoreId($storeId);
        if (self::documentNotExists($result))
            self::insertStoreViewCount($storeId, $ip);
        else
            self::updateStoreViewCount($result, $storeId, $ip);
    }

    /**
     * @return array
     */
    public function showAll()
    {
        $params = [
            'index' => 'wego' . Carbon::now()->toDateString(),
            'type' => 'storeViewCount',
            'body' => [
                'query' => [
                    'match_all' => []
                ]
            ],
        ];
        $client = ClientBuilder::create()->build();
        $result = $client->search($params);
        return $result;
    }

    private static function indexExists()
    {
        $client = ClientBuilder::create()->build();
        $params['index'] = 'wego' . Carbon::now()->toDateString();
        return $client->indices()->exists($params);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public static function countVisitors($storeId)
    {
        $params = [
            'index' => 'wego' . Carbon::now()->subDay(1)->toDateString(),
            'type' => 'storeViewCount',
            "size" => 0,
            "body" => ["aggs" => [
                "filtered_entities" => [
                    "filter" => [
                        "bool" => [
                            "must" => [
                                "term" => ["store_id" => $storeId]
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
     * @return mixed
     */
    public static function calculateAllStoresVisitorCount()
    {
        $stores = Store::all(['id']);
        foreach ($stores as $store) {
            $count[$store->id] = self::countVisitors($store->id);
            self::addCountToElastic($store->id, $count[$store->id]);
        }
        return $count;
    }

    /**
     *
     */
    public function deleteAll()
    {
        $params = [
            'index' => 'wego' . Carbon::now()->toDateString(),
            'type' => 'storeViewCount',
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
     * @param $storeId
     * @param $count
     */
    public static function addCountToElastic($storeId, $count)
    {
        $result = Store::find($storeId);
        $result->view_count += $count;
        $result->save();
        Store::where('id',$storeId)->elastic()->addToIndex();
    }

}