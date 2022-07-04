<?php

use App\Store;
use Illuminate\Database\Seeder;

class StoreMappingCreator extends Seeder
{
    const TYPE = 'stores';
    const INDEX = 'wego_1';


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        self::setMapping();
        Store::elastic()->addToIndex();

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
                "categories" => [
                    "properties" => [
                        "created_at" => [
                            "type" => "date",
                            "format" => "yyyy-MM-dd HH:mm:ss"
                        ],
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
                        ],
                        "updated_at" => [
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
                "departments" => [
                    "properties" => [
                        "department_name" => [
                            "type" => "string"
                        ],
                        "id" => [
                            "type" => "long"
                        ],
                        "pivot" => [
                            "properties" => [
                                "created_at" => [
                                    "type" => "date",
                                    "format" => "yyyy-MM-dd HH:mm:ss"
                                ],
                                "department_email" => [
                                    "type" => "string"
                                ],
                                "department_id" => [
                                    "type" => "long"
                                ],
                                "department_manager_first_name" => [
                                    "type" => "string"
                                ],
                                "department_manager_last_name" => [
                                    "type" => "string"
                                ],
                                "department_manager_picture" => [
                                    "type" => "string"
                                ],
                                "department_phone_number" => [
                                    "type" => "string"
                                ],
                                "department_prefix_phone_number" => [
                                    "type" => "string"
                                ],
                                "store_id" => [
                                    "type" => "long"
                                ],
                                "updated_at" => [
                                    "type" => "string"
                                ]
                            ]
                        ]
                    ]
                ],
                "english_name" => [
                    "type" => "string",
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
                        "created_at" => [
                            "type" => "date",
                            "format" => "yyyy-MM-dd HH:mm:ss"
                        ],
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
                        ],
                        "updated_at" => [
                            "type" => "string"
                        ]
                    ]
                ],
                "province_id" => [
                    "type" => "string"
                ],
                "store_phones" => [
                    "properties" => [
                        "created_at" => [
                            "type" => "date",
                            "format" => "yyyy-MM-dd HH:mm:ss"
                        ],
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
                        ],
                        "type" => [
                            "type" => "long"
                        ],
                        "updated_at" => [
                            "type" => "string"
                        ]
                    ]
                ],
                "pictures" => [
                    "properties" => [
                        "created_at" => [
                            "type" => "date",
                            "format" => "yyyy-MM-dd HH:mm:ss"
                        ],
                        "id" => [
                            "type" => "long"
                        ],
                        "path" => [
                            "type" => "string"
                        ],
                        "store_id" => [
                            "type" => "long"
                        ],
                        "type" => [
                            "type" => "string"
                        ],
                        "updated_at" => [
                            "type" => "string"
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
                        "name" => [
                            "type" => "string",
                        ],
                        "userable_id" => [
                            "type" => "long"
                        ]
                    ]
                ],
                "wego_expiration" => [
                    "type" => "long"
                ],
                "work_times" => [
                    "properties" => [
                        "closing_time" => [
                            "type" => "long"
                        ],
                        "created_at" => [
                            "type" => "date",
                            "format" => "yyyy-MM-dd HH:mm:ss"
                        ],
                        "day" => [
                            "type" => "string"
                        ],
                        "id" => [
                            "type" => "long"
                        ],
                        "is_closed" => [
                            "type" => "string"
                        ],
                        "opening_time" => [
                            "type" => "long"
                        ],
                        "store_id" => [
                            "type" => "long"
                        ],
                        "updated_at" => [
                            "type" => "string"
                        ]
                    ]
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
