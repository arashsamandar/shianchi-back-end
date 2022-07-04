<?php

use App\Product;
use Illuminate\Database\Seeder;

class ProductMappingCreator extends Seeder
{
    const TYPE = 'products';
    const INDEX = 'wego_1';


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        self::setMapping();
        Product::where('category_id',195)->elastic()->addToIndex();
    }

    private static function setMapping()
    {
        $mapping = self::getMapping();
        if (self::isTypeExist())
            self::deleteMapping();
        self::map($mapping);
        return true;
    }

    private static function getMapping()
    {
        return [
                "properties" => [
                    "category" => [
                        "properties" => [
                            "english_path" => [
                                "type" => "string",
                                "index" => "not_analyzed"
                            ],
                            "id" => [
                                "type" => "long"
                            ],
                            "isLeaf" => [
                                "type" => "long"
                            ],
                            "name" => [
                                "type" => "string",
                                "index" => "not_analyzed"
                            ],
                            "path" => [
                                "type" => "string",
                                "index" => "not_analyzed"
                            ],
                            "persian_name" => [
                                "type" => "string",
                                "index" => "not_analyzed"
                            ],
                            "unit" => [
                                "type" => "string"
                            ]
                        ]
                    ],
                    "category_id" => [
                        "type" => "long"
                    ],
                    "colors" => [
                        "properties" => [
                            "code" => [
                                "type" => "string"
                            ],
                            "english_name" => [
                                "type" => "string",
                            ],
                            "id" => [
                                "type" => "long"
                            ],
                            "persian_name" => [
                                "type" => "string",
                            ],
                            "pivot" => [
                                "properties" => [
                                    "color_id" => [
                                        "type" => "long"
                                    ],
                                    "product_id" => [
                                        "type" => "long"
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "confirmation_status" => [
                        "type" => "string"
                    ],
                    "created_at" => [
                        "type" => "date",
                        "format" => "yyyy-MM-dd HH:mm:ss"
                    ],
                    "current_price" => [
                        "type" => "long"
                    ],
                    "english_name" => [
                        "type" => "string"
                    ],
                    "id" => [
                        "type" => "long"
                    ],
                    "key_name" => [
                        "type" => "string"
                    ],
                    "persian_name" => [
                        "type" => "string",
                    ],
                    "pictures" => [
                        "properties" => [
                            "id" => [
                                "type" => "long"
                            ],
                            "path" => [
                                "type" => "string"
                            ],
                            "type" => [
                                "type" => "long"
                            ],
                            "product_id" => [
                                "type" => "long"
                            ]
                        ]
                    ],
                    "quantity" => [
                        "type" => "long"
                    ],
                    "special_conditions" => [
                        "properties" => [
                            "amount" => [
                                "type" => "long"
                            ],
                            "expiration" => [
                                "type" => "string"
                            ],
                            "id" => [
                                "type" => "long"
                            ],
                            "product_id" => [
                                "type" => "long"
                            ],
                            "text" => [
                                "type" => "string"
                            ],
                            "type" => [
                                "type" => "string"
                            ],
                            "upper_value" => [
                                "type" => "long"
                            ],
                            "upper_value_type" => [
                                "type" => "string"
                            ]
                        ]
                    ],
                    "store" => [
                        "properties" => [
                            "bazaar" => [
                                "type" => "string"
                            ],
                            "categories" => [
                                "properties" => [
                                    "english_path" => [
                                        "type" => "string",
                                        "index" => "not_analyzed"
                                    ],
                                    "id" => [
                                        "type" => "long"
                                    ],
                                    "isLeaf" => [
                                        "type" => "long"
                                    ],
                                    "name" => [
                                        "type" => "string",
                                        "index" => "not_analyzed"
                                    ],
                                    "path" => [
                                        "type" => "string",
                                        "index" => "not_analyzed"
                                    ],
                                    "persian_name" => [
                                        "type" => "string",
                                        "index" => "not_analyzed"
                                    ],
                                    "pivot" => [
                                        "properties" => [
                                            "category_id" => [
                                                "type" => "long"
                                            ],
                                            "store_id" => [
                                                "type" => "long"
                                            ]
                                        ]
                                    ],
                                    "unit" => [
                                        "type" => "string"
                                    ]
                                ]
                            ],
                            "city" => [
                                "type" => "string"
                            ],
                            "city_id" => [
                                "type" => "long"
                            ],
                            "created_at" => [
                                "type" => "date",
                                "format" => "yyyy-MM-dd HH:mm:ss"
                            ],
                            "english_name" => [
                                "type" => "string",
                                "index" => "not_analyzed"
                            ],
                            "id" => [
                                "type" => "long"
                            ],
                            "information" => [
                                "type" => "string"
                            ],
                            "location" => [
                                "type" => "geo_point"
                            ],
                            "manager_mobiles" => [
                                "properties" => [
                                    "id" => [
                                        "type" => "long"
                                    ],
                                    "phone_number" => [
                                        "type" => "string"
                                    ],
                                    "prefix_phone_number" => [
                                        "type" => "string"
                                    ],
                                    "store_id" => [
                                        "type" => "long"
                                    ]
                                ]
                            ],
                            "province_id" => [
                                "type" => "string"
                            ],
                            "store_phones" => [
                                "properties" => [
                                    "id" => [
                                        "type" => "long"
                                    ],
                                    "phone_number" => [
                                        "type" => "string"
                                    ],
                                    "prefix_phone_number" => [
                                        "type" => "string"
                                    ],
                                    "store_id" => [
                                        "type" => "long"
                                    ]
                                ]
                            ],
                            "pictures" => [
                                "properties" => [
                                    "path" => [
                                        "type" => "string"
                                    ],
                                    "store_id" => [
                                        "type" => "long"
                                    ]
                                ]
                            ],
                            "updated_at" => [
                                "type" => "string"
                            ],
                            "url" => [
                                "type" => "string",
                                "index" => "not_analyzed"
                            ],
                            "user" => [
                                "properties" => [
                                    "email" => [
                                        "type" => "string"
                                    ],
                                    "id" => [
                                        "type" => "long"
                                    ],
                                    "name" => [
                                        "type" => "string",
                                        "index" => "not_analyzed"
                                    ],
                                    "userable_id" => [
                                        "type" => "long"
                                    ],
                                    "userable_type" => [
                                        "type" => "string"
                                    ]
                                ]
                            ],
                            "wego_expiration" => [
                                "type" => "long"
                            ]
                        ]
                    ],
                    "store_id" => [
                        "type" => "long"
                    ],
                    "updated_at" => [
                        "type" => "string"
                    ],
                    "values" => [
                        "properties" => [
                            "id" => [
                                "type" => "long"
                            ],
                            "name" => [
                                "type" => "string"
                            ],
                            "pivot" => [
                                "properties" => [
                                    "product_id" => [
                                        "type" => "long"
                                    ],
                                    "value_id" => [
                                        "type" => "long"
                                    ]
                                ]
                            ],
                            "specification" => [
                                "properties" => [
                                    "categories" => [
                                        "properties" => [
                                            "id" => [
                                                "type" => "long"
                                            ],
                                            "pivot" => [
                                                "properties" => [
                                                    "category_id" => [
                                                        "type" => "long"
                                                    ],
                                                    "for_buy" => [
                                                        "type" => "long"
                                                    ],
                                                    "important" => [
                                                        "type" => "long"
                                                    ],
                                                    "specification_id" => [
                                                        "type" => "long"
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                    "id" => [
                                        "type" => "long"
                                    ],
                                    "name" => [
                                        "type" => "string"
                                    ]
                                ]
                            ],
                            "specification_id" => [
                                "type" => "long"
                            ]
                        ]
                    ],
                    "view_count" => [
                        "type" => "double"
                    ],
                    "average_score" => [
                        "type" => "double"
                    ],
                    "warranty_name" => [
                        "type" => "string"
                    ],
                    "wego_coin_need" => [
                        "type" => "long"
                    ],
                    "exist_status" => [
                        "type" => "string"
                    ],
                    "weight" => [
                        "type" => "long"
                    ]
                ]
            ];
    }

    private static function isTypeExist()
    {
        $client = \Elasticsearch\ClientBuilder::create()->build();

        return ($client->indices()->existsType(['type' => self::TYPE, 'index' => self::INDEX]));

    }

    private static function deleteMapping()
    {
        $client = \Elasticsearch\ClientBuilder::create()->build();
        $param = [
            'index' => self::INDEX,
            'type' => self::TYPE,
        ];
        $client->indices()->deleteMapping($param);
    }

    private static function map($mapping)
    {
        $client = \Elasticsearch\ClientBuilder::create()->build();

        $param = [
            'index' => self::INDEX,
            'type' => self::TYPE,
            'body' => [
                self::TYPE => [
                    '_source' => [
                        'enable' => true
                    ],
                    'properties' => $mapping['properties']
                ]
            ]
        ];
        $client->indices()->putMapping($param);
    }
}
