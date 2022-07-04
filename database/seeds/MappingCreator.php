<?php

use App\Category;
use App\Product;
use App\Store;
use Illuminate\Database\Seeder;

class MappingCreator extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $documentsToIndex = ['categories'];

        foreach ($documentsToIndex as $document) {
            self::setMapping($document);
        }


//        Store::elastic()->addToIndex();

//        for ($i =17; $i<18; $i++){
//            Product::where([['id','>=',$i*500], ['id','<',($i+1)*500]])->elastic()->addToIndex();
//            sleep(10);
//        }

//        Product::where([['id','>=',1], ['id','<',2000]])->elastic()->addToIndex();

           //Category::elastic()->addToIndex();

//        $documentsToIndex = ['orders', 'categories', 'products', 'message'];
//
//        foreach ($documentsToIndex as $document) {
//            self::setMapping($document);
//        }
//
//
//        Store::elastic()->addToIndex();
//
//        Product::elastic()->get()->addToIndex();
//
        Category::elastic()->addToIndex();

    }


    public static function setMapping($type)
    {
        $functionName = self::getFunctionName($type);
        $mapping = self::$functionName();
        if (self::isTypeExist($type))
            self::deleteMapping($type);
        self::map($type, $mapping);
        return true;
    }

    private static function getMessageMapping()
    {
        return [
            "properties" => [
                "sending_time" => [
                    "type" => "date",
                    "format" => "yyyy-MM-dd HH:mm:ss"
                ]
            ]
        ];
    }

    private static function getProductsMapping()
    {
        return
            [
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
                        "type" => "long"
                    ],
                    "warranty_name" => [
                        "type" => "string"
                    ],
                    "wego_coin_need" => [
                        "type" => "long"
                    ],
                    "weight" => [
                        "type" => "long"
                    ]
                ]
            ];
    }

    private static function getOrdersMapping()
    {
        return [
            "properties" => [
                "address" => [
                    "properties" => [
                        "address" => [
                            "type" => "string"
                        ],
                        "id" => [
                            "type" => "long"
                        ],
                        "city" => [
                            "type" => "string"
                        ],
                        "province" => [
                            "type" => "string"
                        ],
                        "receiver_first_name" => [
                            "type" => "string"
                        ],
                        "receiver_last_name" => [
                            "type" => "string"
                        ],
                        "receiver_mobile" => [
                            "type" => "string"
                        ],
                        "receiver_prefix_mobile_number" => [
                            "type" => "string"
                        ],
                        "receiver_prefix_phone_number" => [
                            "type" => "string"
                        ],
                        "receiver_phone" => [
                            "type" => "string"
                        ],
                        "total_discount" => [
                            "type" => "long"
                        ],
                        "total_price" => [
                            "type" => "long"
                        ],
                        "total_wego_coin_use" => [
                            "type" => "long"
                        ],
                        "final_price" => [
                            "type" => "long"
                        ],
                        "postal_code" => [
                            "type" => "string"
                        ]
                    ]
                ],
                "created_at" => [
                    "type" => "date",
                    "format" => "yyyy-MM-dd HH:mm:ss"
                ],
                "delivery_date" => [
                    "type" => "string"
                ],
                "delivery_time" => [
                    "type" => "string"
                ],
                "shamsi_delivery_time" => [
                    "type" => "string"
                ],
                "shamsi_day" => [
                    "type" => "string"
                ],
                "discount" => [
                    "type" => "long"
                ],
                "payment_id" => [
                    "type" => "long"
                ],
                "id" => [
                    "type" => "long"
                ],
                "total_price" => [
                    "type" => "long"
                ],
                "final_price" => [
                    "type" => "long"
                ],
                "total_discount" => [
                    "type" => "long"
                ],
                "total_wego_coin_used" => [
                    "type" => "long"
                ],
                "shipping_company" => [
                    "type" => "string"
                ],
                "shipping_price" => [
                    "type" => "long"
                ],
                "shipping_status" => [
                    "type" => "string"
                ],
                "status" => [
                    "type" => "long"
                ],
                "progressable" => [
                    "type" => "boolean"
                ],
                "stores" => [
                    "type" => "nested",
                    "properties" => [
                        "bazaar" => [
                            "type" => "string"
                        ],
                        "bazaar_staff" => [
                            "properties" => [
                                "first_name" => [
                                    "type" => "string"
                                ],
                                "last_name" => [
                                    "type" => "string"
                                ],
                                "mobile" => [
                                    "type" => "string"
                                ]
                            ]
                        ],
                        "city_id" => [
                            "type" => "string"
                        ],
                        "english_name" => [
                            "type" => "string"
                        ],
                        "id" => [
                            "type" => "string"
                        ],
                        "persian_name" => [
                            "type" => "string"
                        ],
                        "phone" => [
                            "type" => "string"
                        ],
                        "products" => [
                            "properties" => [
                                "category_unit" => [
                                    "type" => "string"
                                ],
                                "color_id" => [
                                    "type" => "long"
                                ],
                                "color_name" => [
                                    "type" => "string"
                                ],
                                "discount" => [
                                    "type" => "long"
                                ],
                                "gift" => [
                                    "type" => "string"
                                ],
                                "gift_count" => [
                                    "type" => "long"
                                ],
                                "gift_amount" => [
                                    "type" => "long"
                                ],
                                "total_price" => [
                                    "type" => "long"
                                ],
                                "english_name" => [
                                    "type" => "string"
                                ],
                                "product_id" => [
                                    "type" => "long"
                                ],
                                "persian_name" => [
                                    "type" => "string"
                                ],
                                "unit_price" => [
                                    "type" => "string"
                                ],
                                "quantity" => [
                                    "type" => "long"
                                ],
                                "specifications" => [
                                    "properties" => [
                                        "name" => [
                                            "type" => "string"
                                        ],
                                        "specification_id" => [
                                            "type" => "string"
                                        ],
                                        "value" => [
                                            "type" => "string"
                                        ],
                                        "value_id" => [
                                            "type" => "long"
                                        ]
                                    ]
                                ],
                                "store_id" => [
                                    "type" => "string"
                                ],
                                "wego_coin_get" => [
                                    "type" => "long"
                                ],
                                "wego_coin_use" => [
                                    "type" => "long"
                                ],
                                "staff_message" => [
                                    "type" => "string"
                                ]
                            ]
                        ],
                        "province_id" => [
                            "type" => "string"
                        ]
                    ]
                ],
                "user_id" => [
                    "type" => "long"
                ],
                "wego_coin_get_ids" => [
                    "type" => "long"
                ],
                "wego_coin_used" => [
                    "properties" => [
                        "product_id" => [
                            "type" => "long"
                        ],
                        "remained_amount" => [
                            "type" => "long"
                        ],
                        "subtracted_amount" => [
                            "type" => "long"
                        ],
                        "wego_coin_id" => [
                            "type" => "long"
                        ]
                    ]
                ]
            ]
        ];
    }

    private static function getStoresMapping()
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

    private static function getCategoriesMapping()
    {
        return [
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
                "lft" => [
                    "type" => "long"
                ],
                "name" => [
                    "type" => "string",
                    "index" => "not_analyzed"
                ],
                "menu_id" => [
                    "type" => "string",
                    "index" => "not_analyzed"
                ],
                "path" => [
                    "type" => "string",
                    "index" => "not_analyzed"
                ],
                "persian_name" => [
                    "type" => "string"
                ],
                "rgt" => [
                    "type" => "long"
                ],
                "specifications" => [
                    "properties" => [
                        "created_at" => [
                            "type" => "date",
                            "format" => "yyyy-MM-dd HH:mm:ss"
                        ],
                        "id" => [
                            "type" => "long"
                        ],
                        "name" => [
                            "type" => "string"
                        ],
                        "updated_at" => [
                            "type" => "string"
                        ],
                        "values" => [
                            "properties" => [
                                "created_at" => [
                                    "type" => "date",
                                    "format" => "yyyy-MM-dd HH:mm:ss"
                                ],
                                "id" => [
                                    "type" => "long"
                                ],
                                "name" => [
                                    "type" => "string"
                                ],
                                "specification_id" => [
                                    "type" => "long"
                                ],
                                "updated_at" => [
                                    "type" => "string"
                                ]
                            ]
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
        ];
    }

    private static function map($type, $mapping = [], $index = 'wego_1')
    {

        $client = \Elasticsearch\ClientBuilder::create()->build();

        $param = [
            'index' => $index,
            'type' => $type,
            'body' => [
                $type => [
                    '_source' => [
                        'enable' => true
                    ],
                    'properties' => $mapping['properties']
                ]
            ]
        ];
        $client->indices()->putMapping($param);

    }

    private static function deleteMapping($type, $index = 'wego_1')
    {
        $client = \Elasticsearch\ClientBuilder::create()->build();
        $param = [
            'index' => $index,
            'type' => $type,
        ];
        $client->indices()->deleteMapping($param);
    }

    private static function getFunctionName($type)
    {
        return 'get' . ucfirst($type) . 'Mapping';
    }

    private static function isTypeExist($type, $index = 'wego_1')
    {
        $client = \Elasticsearch\ClientBuilder::create()->build();

        return ($client->indices()->existsType(['type' => $type, 'index' => $index]));

    }
}
