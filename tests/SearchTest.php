<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SearchTest extends TestCase
{
    /** @test */
    public function search_in_search_page(){
        $result=$this->action('GET','SearchController@index',['discount'=>true,'gift'=>true,'filter1'=>'1_2_3_4',
            'category'=>'electronics','sortBy'=>1003,'sortFor'=>1,'store_id'=>11,'keyword'=>'custom','from'=>0],[],[],[]);
        $decoded_result=\GuzzleHttp\json_decode($result->content(),true);
        $this->assertTrue($decoded_result['hits']['total'] !== 0);
        $this->assertTrue($decoded_result['hits']['hits'][0]['_id'] == 101);
    }
    /** @test */
    public function search_in_products(){
        $result=$this->action('POST','SearchController@product',['name'=>'custom'],[],[],[]);
        $decoded_result=\GuzzleHttp\json_decode($result->content(),true)[""];
        foreach($decoded_result as $product){
            $this->assertTrue(strpos($product['_source']['english_name'],'custom') !== false);
        }
    }
    /** @test */
    public function search_in_category(){
        $result=$this->action('GET','SearchController@searchCategoryByName',['category_name'=>'television'],[],[],[]);
        $decoded_result=\GuzzleHttp\json_decode($result->content(),true);
        //dd($decoded_result);
        $this->assertTrue(true);
    }

    /** @test */
    public function search_keyword(){
        $result=$this->action('GET','SearchController@keyword',['keyword'=>'custom'],[],[],[]);
        $this->assertTrue(true);
        //dd(\GuzzleHttp\json_decode($result->content(),true));
    }
    /** @test */
    public function search_store_by_store_keyword(){
        $result=$this->action('GET','SearchController@storeKeyword',['keyword'=>'store'],[],[],[]);
        //dd(\GuzzleHttp\json_decode($result->content(),true));
        $this->assertTrue(true);
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }
}
