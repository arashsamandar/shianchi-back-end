<?php

use App\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductScoreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('product_score')->where('product_id','>',2272)->where('product_id','<',4451)->where('user_id',23)->delete();
        $request= [];
        $products= Product::where('id','>',2272)->where('id','<',4451)->pluck('id');
        foreach ($products as $productId){
            $request[]= ['user_id'=>23, 'product_id' =>$productId , 'score' => rand(3,5),
                'score_title_id' => 1 , 'created_at'=>\Carbon\Carbon::now() , 'updated_at'=>\Carbon\Carbon::now()];
            $request[]= ['user_id'=>23, 'product_id' =>$productId , 'score' => rand(3,5),
                'score_title_id' => 2 , 'created_at'=>\Carbon\Carbon::now() , 'updated_at'=>\Carbon\Carbon::now()];
            $request[]= ['user_id'=>23, 'product_id' =>$productId , 'score' => rand(3,5),
                'score_title_id' => 3 , 'created_at'=>\Carbon\Carbon::now() , 'updated_at'=>\Carbon\Carbon::now()];
            $request[]= ['user_id'=>23, 'product_id' =>$productId , 'score' => rand(3,5),
                'score_title_id' => 4 , 'created_at'=>\Carbon\Carbon::now() , 'updated_at'=>\Carbon\Carbon::now()];
        }
        foreach(array_chunk($request,1000) as $input){
            DB::table('product_score')->insert($input);
        }
    }
}
