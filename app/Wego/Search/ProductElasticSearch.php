<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 06/09/17
 * Time: 16:59
 */

namespace Wego\Search;


use App\Product;
use Dingo\Api\Exception\ValidationHttpException;
use Elasticsearch\ClientBuilder;

class ProductElasticSearch
{
    private $client;

    function __construct()
    {
        $this->client = ClientBuilder::create()->build();
    }

    public function withCategory($categoryName, $size = 10)
    {
        $param =
            [
                'index' => 'wego_1',
                'type' => 'products',
                'size' => $size,
                'body' =>
                    [
                        'query' => [
                            "bool" => [
                                "must" => [
                                    ["term" => ["category.name" => strtolower($categoryName)]],
                                    ["term" => ["exist_status" => Product::EXISTS]],
                                ]
                            ]
                        ],
                        'sort' => [
                            "view_count" => [
                                "order" => "desc"
                            ]
                        ]
                    ],
                '_source' =>
                    [
                        'id', 'english_name', 'persian_name', 'pictures', 'current_price',
                        "special_conditions.type", "special_conditions.amount",
                        "special_conditions.expiration"
                    ]
            ];
        return $this->client->search($param);
    }

    public function withCategoryId($categoryId,$size=10)
    {
        $param =
            [
                'index' => 'wego_1',
                'type' => 'products',
                'size' => $size,
                'body' =>
                    [
                        'query' => [
                            "bool" => [
                                "must" => [
                                    ["term" => ["category_id" => $categoryId]],
                                    ["term" => ["exist_status" => Product::EXISTS]],
                                ]
                            ]
                        ],
                        'sort' => [
                            "view_count" => [
                                "order" => "desc"
                            ]
                        ]
                    ],
                '_source' =>
                    [
                        'id', 'english_name', 'persian_name', 'pictures', 'current_price',
                        "special_conditions.type", "special_conditions.amount",
                        "special_conditions.expiration"
                    ]
            ];
        return $this->client->search($param);
    }

    public function mostViewed($size = 20)
    {
        $param = [
            'index' => 'wego_1',
            'type' => 'products',
            'size' => $size,
            'body' => [
                'query' => [
                    'term' => [
                        'exist_status' => Product::EXISTS
                    ]
                ],
                'sort' => [
                    "view_count" => [
                        "order" => "desc"
                    ]
                ]
            ],
            '_source' => ['id', 'english_name', 'persian_name', 'pictures', 'current_price', "special_conditions.type", "special_conditions.amount",
                "special_conditions.expiration"]
        ];
        try {
            return $this->client->search($param);
        } catch (\Exception $e) {
            return ['hits' => ['hits' => []]];
        }
    }

    public function mostSell($size = 20)
    {
        $param = [
            'index' => 'wego_1',
            'type' => 'products',
            'size' => $size,
            'body' => [
                'query' => [
                    'term' => [
                        'exist_status' => Product::EXISTS
                    ]
                ],
                'sort' => [
                    "sale" => [
                        "order" => "desc"
                    ]
                ]
            ],
            '_source' =>
                [
                    'id', 'english_name', 'persian_name', 'pictures', 'current_price',
                    "special_conditions.type", "special_conditions.amount",
                    "special_conditions.expiration"
                ]
        ];
        try {
            return $this->client->search($param);
        } catch (\Exception $e) {
            return ['hits' => ['hits' => []]];
        }
    }

    public function recentlyAdded($size = 20)
    {
        $param = [
            'index' => 'wego_1',
            'type' => 'products',
            'size' => $size,
            'body' => [
                'query' => [
                    'term' => [
                        'exist_status' => Product::EXISTS
                    ]
                ],
                'sort' => [
                    "created_at" => [
                        "order" => "desc"
                    ]
                ]
            ],
            '_source' => [
                'id', 'english_name', 'persian_name', 'pictures',
                'current_price', "special_conditions.type",
                "special_conditions.amount", "special_conditions.expiration"
            ]
        ];
        return $this->client->search($param);
    }

    public function recommended($recommends, $size = 10)
    {
        $param = [
            'index' => 'wego_1',
            'type' => 'products',
            'size' => $size,
            'body' => [
                'query' => [
                    'filtered' => [
                        'query' => [
                            'term' =>
                                [
                                    'exist_status' => Product::EXISTS
                                ]
                        ],
                        'filter' => ['bool' => ['must' => ['terms' => ['id' => $recommends]]]]],
                ],
                'sort' => [
                    "sale" => [
                        "order" => "desc"
                    ]
                ]
            ],
            '_source' => [
                'id', 'english_name', 'persian_name', 'pictures',
                'current_price', 'special_conditions.type',
                'special_conditions.amount', 'special_conditions.expiration'
            ]
        ];
        try {
            $result = $this->client->search($param);
            if ($result['hits']['total'] == 0){
                throw new ValidationHttpException();
            }
            return $result;
        } catch (\Exception $e) {
            return ['hits' => ['hits' => []]];
        }
    }

    public function byId($id)
    {
        $query =
            [
                'index' => 'wego_1',
                'type' => 'products',
                'body' =>
                    [
                        "filter" => ["query" => ["ids" => ["values" => [$id]]]],
                    ],
            ];
        return $this->client->search($query);
    }

    public function dailyOffer($size=20)
    {
        $param = [
            'index' => 'wego_1',
            'type' => 'products',
            'size' => $size,
            'body' => [
                'query' => [
                    'regexp' => [
                        'key_name' => ".*dailyoffer.*"
                    ]
                ],
                'sort' => [
                    "sale" => [
                        "order" => "desc"
                    ]
                ]
            ],
            '_source' =>
                [
                    'id', 'english_name', 'persian_name', 'pictures', 'current_price',
                    "special_conditions.type", "special_conditions.amount",
                    "special_conditions.expiration"
                ]
        ];
        try {
            return $this->client->search($param);
        } catch (\Exception $e) {
            return ['hits' => ['hits' => []]];
        }
    }


}