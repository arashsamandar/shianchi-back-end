<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('color_product')->insert(
            [
                [
                    'color_id' => '1',
                    'product_id' => '36'
                ],
                [
                    'color_id' => '2',
                    'product_id' => '36'
                ],
                [
                    'color_id' => '3',
                    'product_id' => '36'
                ],
                [
                    'color_id' => '4',
                    'product_id' => '36'
                ],
            ]
        );
    }
}
