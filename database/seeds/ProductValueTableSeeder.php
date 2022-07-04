<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductValueTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('product_value')->insert([

            [
                'product_id'=>35,
                "value_id" => 1
            ],
            [
                'product_id'=>35,
                "value_id" => 2
            ],
            [
                'product_id'=>35,
                "value_id" => 3
            ],
            [
                'product_id'=>35,
                "value_id" => 4
            ],
            [
                'product_id'=>35,
                "value_id" => 5
            ],
            [
                'product_id'=>35,
                "value_id" => 6
            ],
            [
                'product_id'=>35,
                "value_id" => 8
            ],
            [
                'product_id'=>36,
                "value_id" => 1
            ],
            [
                'product_id'=>36,
                "value_id" => 2
            ],
            [
                'product_id'=>36,
                "value_id" => 5
            ],
            [
                'product_id'=>36,
                "value_id" => 8
            ],
            
        ]);
    }
}
