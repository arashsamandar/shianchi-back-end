<?php

use App\Category;
use Illuminate\Database\Seeder;

class CategoryMappingCreator extends Seeder
{
    const TYPE = 'categories';
    const INDEX = 'wego_1';


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        self::setMapping();
        Category::elastic()->addToIndex();
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
