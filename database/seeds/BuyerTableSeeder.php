<?php

use Illuminate\Database\Seeder;

class BuyerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\Buyer',10)
            ->create()
            ->each(function($u){
                $u->user()->save(factory('App\User')->make());
                for($i = 0; $i < 3; ++$i)
                    $u->user->wegoCoin()->save(factory('App\WegoCoin')->make());
                $u->user->addresses()->save(factory('App\BuyerAddress')->make());
            });
        $this->createCustomBuyer();
    }

    private function createCustomBuyer(){
        $buyer = \App\Buyer::create([
            "last_name" => "dicaprio",
            "national_code" => "0550350509",
            "mobile_number" => "09124247487",
            "landline_number" => "02177012772",
            "address" => "amirabad amirabad amirabad amirabad amirabad",
            "image_path" => "alaki",
            "company_name" => "tesla motors",
            "card_number" => "1234-1234-1234-1234",
        ]);
        $buyer->user()->create(['name'=>'buyer','email'=>'buyer@1.com','password'=>bcrypt(12)]);
        $buyer->user->wegoCoin()->create([
            'store_id' => 11,
            'status' => 'a',
            'amount' => 4,
            'expiration' => '2016-07-25'
        ]);
        $buyer->user->wegoCoin()->create([
            'store_id' => 11,
            'status' => 'a',
            'amount' => 18,
            'expiration' => '2016-07-25'
        ]);
        $buyer->user->wegoCoin()->create([
            'store_id' => 12,
            'status' => 'a',
            'amount' => 13,
            'expiration' => '2016-07-25'
        ]);
        for($i = 0; $i < 3; ++$i)
        $buyer->user->addresses()->save(factory('App\BuyerAddress')->make());
    }
}
