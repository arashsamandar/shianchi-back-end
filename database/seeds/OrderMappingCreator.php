<?php

use Illuminate\Database\Seeder;

class OrderMappingCreator extends Seeder
{
    const TYPE = 'orders';
    const INDEX = 'wego_1';


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        self::setMapping();
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
