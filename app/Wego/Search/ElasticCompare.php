<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 01/05/16
 * Time: 07:43
 */

namespace Wego\Search;
use Elasticsearch\ClientBuilder;

use App\Http\Requests;

class ElasticCompare
{
    const SIZE = 10;

    protected  $request,$source;
    function __construct( $request)
    {
        $this->source = "*";
        $this->request = $request;
    }
    public function run()
    {
        $url = $this->request;
        $client = ClientBuilder::create()->build();
        $result = $client->search($this->createQuery($url));
        return array_map([$this,'mapProduct'],$result['hits']['hits']);
    }

    public function createQuery($url)
    {
        return
            [
                'index' => 'wego_1',
                'type' => 'products',
                'body' =>
                    [
                        "filter"=>["query"=>["ids"=>["values"=>$url]]],
                    ],
                "_source"=>[$this->source]
            ];
    }

    public function setSource($source){
        $this->source = $source;
        return $this;
    }
    private function mapProduct($item)
    {

        $condition = collect($item["_source"]["special_conditions"])->groupBy('type')->toArray();

        $gift = isset($condition["gift"][0])? $this->specialMaker($condition["gift"][0]) : null;
        $discount = (isset($condition["discount"][0]))? $this->specialMaker($condition["discount"][0]) : null;
        $wegoCOin = (isset($condition["wego_coin"][0]))? $this->specialMaker($condition["wego_coin"][0]) : null;

        $spec = array_map([$this,'mapSpec'],$item["_source"]["values"]);

        return [
            "main"=>[
                "price" => $item["_source"]["current_price"],
                "warranty" =>$item["_source"]["warranty_text"],
                "gift"=> $gift[0],
                "english_name"=> $item["_source"]["english_name"],
                "persian_name" => $item["_source"]["persian_name"],
                "discount" => $discount[0],
                "wego_coin" => $wegoCOin[0],
                "path" => $item["_source"]["pictures"][0]["path"],
                "id" => $item["_source"]["id"]
            ],
            "store" =>[
                "name" => $item["_source"]["store"]["user"]["name"],
                "phone" => $item["_source"]["store"]["store_phones"][0]["prefix_phone_number"].$item["_source"]["store"]["store_phones"][0]["phone_number"],
                "address" => $item["_source"]["store"]["address"],
                "information" => $item["_source"]["store"]["information"],
                "path" => $item["_source"]["store"]["pictures"][0]["path"],
            ],
            "spec"=>$spec
        ];
    }
    private function mapSpec($item)
    {
        return [
            "key" =>$item["specification"]["name"],
            "value"=>$item["name"]
        ];
    }

    private function specialMaker($item)
    {
        return [
            'بالای'.$item["upper_value"].' '.$item["upper_value_type"]
        ];
    }
}