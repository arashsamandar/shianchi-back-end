<?php

use Illuminate\Database\Seeder;

class ColorTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
           factory('App\Color')->create([
                "persian_name"=> "سفید",
                "code"=> "#ffffff",
                "english_name"=>"white"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "مشکی",
                "code"=> "#000000",
                "english_name"=>"black"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "طلایی",
                "code"=> "#ffd700",
                "english_name"=>"gold"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "نقره ای",
                "code"=> "#eeeeee",
                "english_name"=>"silver"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "خاکستری",
                "code"=> "#cccccc",
                "english_name"=>"gray"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "نوک مدادی",
                "code"=> "#666666",
                "english_name"=>"dark-gray"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "صورتی",
                "code"=> "#ffc0cb",
                "english_name"=>"pink"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "سرمه ای",
                "code"=> "#003366",
                "english_name"=>"dark-blue"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "کرم",
                "code"=> "#faebd7",
                "english_name"=>"beige"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "قهوه ای",
                "code"=> "#800000",
                "english_name"=>"brown"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "آبی",
                "code"=> "#0000ff",
                "english_name"=>"blue"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "زرشکی",
                "code"=> "#cc0000",
                "english_name"=>"dark-red"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "سبز",
                "code"=> "#00ff00",
                "english_name"=>"green"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "قرمز",
                "code"=> "#ff0000",
                "english_name"=>"red"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "نارنجی",
                "code"=> "#ffa500",
                "english_name"=>"orange"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "فیروزه ای",
                "code"=> "#00ffff",
                "english_name"=>"turquoise"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "گلبهی",
                "code"=> "#ffc3a0",
                "english_name"=>"peach"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "آبی کم رنگ",
                "code"=> "#c6e2ff",
                "english_name"=>"light-blue"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "سبز تیره",
                "code"=> "#008000",
                "english_name"=>"dark-green"
            ]);
           factory('App\Color')->create([
                "persian_name"=> "بنفش",
                "code"=> "#800080",
                "english_name"=>"violet"
           ]);


    }
}
