<?php

use Illuminate\Database\Seeder;
use App\ShippingCompany;
use Illuminate\Support\Facades\DB;

class ShippingCompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shipping_companies')->insert([
            [
                'persian_name'=> 'ارسال ویژه ویگوبازار',
                'path' => '/wego/ShippingPrice/wegobazaar.jpg',
                'company'=>'wegobazaar',
                'link'=>'/privacy'
            ],
            [
                'persian_name'=> 'پست',
                'path' => '/wego/ShippingPrice/post.jpg',
                'company'=>'post',
                'link'=>'/privacy'

            ],
            [
                'persian_name'=> 'تیپاکس',
                'path' => '/wego/ShippingPrice/tipax.jpg',
                'company'=>'tipax',
                'link'=>'/privacy'

            ],
            [
                'persian_name'=> 'ارسال ویژه ویگوبازار',
                'path' => '/wego/shipping/aramex.jpg',
                'company'=>'wegobazaar',
                'link'=>'/privacy'

            ],

        ]);

    }
}
