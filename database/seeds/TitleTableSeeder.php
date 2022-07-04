<?php

use Illuminate\Database\Seeder;

class TitleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\Title')->create([
            'title' => 'مشخصات کلی',
            //'category_id' => 9
        ]);
        factory('App\Title')->create([
            'title' => 'ظرفیت',
            //'category_id' => 9
        ]);
        factory('App\Title')->create([
            'title' => 'سایر مشخصات',
            //'category_id' => 9
        ]);
        factory('App\Title')->create([
            'title' => 'صفحه نمایش',
            //'category_id' => 5
        ]);
        ////5
        factory('App\Title')->create([
            'title' => 'مشخصات ماشین',
//            'category_id' => 4
        ]);

        factory('App\Title')->create([
            'title' => 'مشخصات راننده',
//            'category_id' => 4
        ]);

    }
}
