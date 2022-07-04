<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BazaarStoreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bazaar_store')->insert(
            [
                [
                    'bazaar_id' => '1',
                    'store_id' => '9'
                ],
                [
                    'bazaar_id' => '2',
                    'store_id' => '3'
                ],
                [
                    'bazaar_id' => '2',
                    'store_id' => '6'
                ],
                [
                    'bazaar_id' => '3',
                    'store_id' => '5'
                ],
                [
                    'bazaar_id' => '3',
                    'store_id' => '6'
                ],
                [
                    'bazaar_id' => '3',
                    'store_id' => '9'
                ],
            ]
        );
    }
}
