<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySpecificationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        DB::table('category_specification')
            ->insert([
                [
                    //"important"=>1,
                    //"for_buy"=>1,
                    "specification_id"=>1,
                    "category_id"=>9
                ],
                [
                    //"important"=>1,
                   // "for_buy"=>1,
                    "specification_id"=>2,
                    "category_id"=>9
                ],
                [
                    //"important"=>0,
                    //"for_buy"=>0,
                    "specification_id"=>3,
                    "category_id"=>9
                ]
            ]);

    }
}
