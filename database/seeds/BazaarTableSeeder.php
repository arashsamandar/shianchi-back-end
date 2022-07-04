<?php

use Illuminate\Database\Seeder;

class BazaarTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bazaars')->insert([
            [
                'name'=>'بازار کتاب',
                'city_id' => 1698,
                'province_id' => 1,
                'address' => 'تهران - خیابان انقلاب'
            ],
        ]);

        DB::table('bazaars')->insert([
            [
                'name'=>'بازار لامپ',
                'city_id' => 1500,
                'province_id' => 1,
                'address' => 'تهران - خیابان انقلاب'
            ],
        ]);



        DB::table('bazaars')->insert([
            [
                'name'=>'بازار اسباب بازی',
                'city_id' => 1695,
                'province_id' => 1,
                'address' => 'تهران - خیابان آزادی'
            ],
        ]);


    }
}
