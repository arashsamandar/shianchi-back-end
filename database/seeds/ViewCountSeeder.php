<?php

use App\Product;
use App\Store;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Database\Seeder;

class ViewCountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $client = ClientBuilder::create()->build();
        $products=Product::all(['id']);
        foreach ($products as $product){
            $params=[
                'index'=>'wego'.Carbon::now()->toDateString(),
                'type'=>'productViewCount',
                'id'=>$product->id,
                'body'=>[
                    'product_id'=>$product->id
                ],
            ];
            $client->index($params);
        }
        $stores=Store::all(['id']);
        foreach ($stores as $store){
            $params=[
                'index'=>'wego'.Carbon::now()->toDateString(),
                'type'=>'storeViewCount',
                'id'=>$store->id,
                'body'=>[
                    'store_id'=>$store->id
                ],
            ];
            $client->index($params);
        }
    }
}
