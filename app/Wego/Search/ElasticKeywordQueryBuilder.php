<?php
namespace Wego\Search;

use Illuminate\Http\Request;
use Wego\Helpers\PersianUtil;
use App\Http\Requests;

class ElasticKeywordQueryBuilder //implements SearchInterface
{
    protected $request;
    const SIZE = 10;

    function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function run()
    {
        return $this->createQuery($this->request->keyword);
    }

    public function createQuery($keyword)
    {
        $standardKeyword = PersianUtil::toStandardPersianString($keyword);
        $item1 = [
            'index' => 'wego_1',
            'type' => ['categories','products'],
            'size' => 1000,
            'body' =>
                [
                    "query" => [
                        "fuzzy_like_this" => [
                            "fields" => ["user.name", "english_name", "persian_name", "key_name"],
                            "like_text" => $standardKeyword,
                            "fuzziness"=> .5,
                            "prefix_length"=>2
                        ]

                    ],
//                    "sort" => [
//                        'isLeaf'=> [
//                            "order"=>'desc'
//                        ],
//                        'view_count'=>[
//                            "order"=>"desc"
//                        ]
//                    ]
                ],
            "_source" => ['english_name', 'name', 'persian_name', 'pictures.path', 'user.name', 'url', 'path','isLeaf']
        ];
        return $item1;

    }

    public function queryFormat()
    {

    }

    public function makeQuery()
    {

    }
}