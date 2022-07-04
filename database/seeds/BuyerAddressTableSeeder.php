<?php

use Illuminate\Database\Seeder;

class BuyerAddressTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\BuyerAddress',10)->create()
            ->each(function($u){
                $u->buyer_addresses()->save(factory('App\BuyerAddress')->make());
            });

    }
}
