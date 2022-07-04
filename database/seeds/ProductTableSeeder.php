<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
class ProductTableSeeder extends Seeder
{
    private $products = [
        0 => [
            'weight'=> 30000,
            'english_name' => 'custom product 1',
            'persian_name' => " 1محصول سیید شده",
            'quantity' => 200,
            'store_id' => 11,
            'category_id' => 1,
            'current_price' => 1000,
            'wego_coin_need' => 5,
            'coin' => true,
            'coin.upper_value' => 3000,
            'coin.upper_value_type' => 'تومان',
            'coin.amount' => 3,
            'gift' => true,
            'gift.upper_value' => 2,
            'gift.upper_value_type' => 'عدد',
            'gift.amount' => 4000,
            'discount' => true,
            'discount.upper_value' => 4,
            'discount.upper_value_type' => 'عدد',
            'discount.amount' => 30,
        ],
        1 => [
            'weight'=> 4500,
            'english_name' => 'custom product 2',
            'persian_name' => " 2 سیید شده",
            'quantity' => 200,
            'store_id' => 11,
            'category_id' => 1,
            'current_price' => 55000,
            'wego_coin_need' => 0,
            'coin' => false,
            'coin.upper_value' => 3000,
            'coin.upper_value_type' => 'تومان',
            'coin.amount' => 3,
            'gift' => true,
            'gift.upper_value' => 50000,
            'gift.upper_value_type' => 'تومان',
            'gift.amount' => 4000,
            'discount' => false,
            'discount.upper_value' => 4,
            'discount.upper_value_type' => 'عدد',
            'discount.amount' => 30,
        ],
        2 => [
            'weight'=> 3300,
            'english_name' => 'custom product 3',
            'persian_name' => " 3 سیید شده",
            'quantity' => 200,
            'store_id' => 12,
            'category_id' => 2,
            'current_price' => 4000,
            'wego_coin_need' => 2,
            'coin' => true,
            'coin.upper_value' => 3000,
            'coin.upper_value_type' => 'تومان',
            'coin.amount' => 3,
            'gift' => true,
            'gift.upper_value' => 1,
            'gift.upper_value_type' => 'عدد',
            'gift.amount' => 60000,
            'discount' => false,
            'discount.upper_value' => 4,
            'discount.upper_value_type' => 'عدد',
            'discount.amount' => 30,
        ]
    ];
    public function run()
    {
//        factory('App\Product',100)->create()
//                ->each(function($u){
//                    for($i = 0; $i < 5; ++$i){
//
//                        $u->pictures()->create([
//                            'type' => $i,
//                            'path' => "/wego/product/150_".random_int(0,7).".png"
//                        ]);
//                    }
//                    for($i = 0; $i < 3; ++$i){
//                        $u->special_conditions()->create(
//                            $this->createSpecial($i)
//                        );
//                    }
//                    $u->store->categories()->sync([$u->category_id],false);
//                });
        $this->createCustomProducts(0);
        $this->createCustomProducts(1);
        $this->createCustomProducts(2);
    }

    private function createCustomProducts($index){
        $product = \App\Product::create([
            'weight'=>$this->products[$index]['weight'],
            'english_name' => $this->products[$index]['english_name'],
            'persian_name' => $this->products[$index]['persian_name'],
            'key_name' => 'key',
            'quantity' => $this->products[$index]['quantity'],
            'store_id' => $this->products[$index]['store_id'],
            'category_id' => 4,
            'current_price' => $this->products[$index]['current_price'],
            'wego_coin_need' => $this->products[$index]['wego_coin_need'],
            'warranty_name' => "گارانتی دو ساله فن آوارهگان",
            'warranty_text' => \Wego\Helpers\PersianFaker::getSentence()
        ]);
        for($i = 0; $i < 5; ++$i){
            $product->pictures()->create([
                'type' => $i,
                'path' => "/wego/product/150_".random_int(0,7).".png"
            ]);
        }
        if($this->products[$index]['gift'])
            $product->special_conditions()->create([
                'type' => 'gift',
                'upper_value' => $this->products[$index]['gift.upper_value'],
                'upper_value_type' => $this->products[$index]['gift.upper_value_type'],
                'amount' => $this->products[$index]['gift.amount'],
                'text' => 'dummy text'
            ]);
        if($this->products[$index]['discount'])
            $product->special_conditions()->create([
                'type' => 'discount',
                'upper_value' => $this->products[$index]['discount.upper_value'],
                'upper_value_type' => $this->products[$index]['discount.upper_value_type'],
                'amount' => $this->products[$index]['discount.amount'],
                'text' => 'dummy text'
            ]);
        if($this->products[$index]['coin'])
            $product->special_conditions()->create([
                'type' => 'wego_coin',
                'upper_value' => $this->products[$index]['coin.upper_value'],
                'upper_value_type' => $this->products[$index]['coin.upper_value_type'],
                'amount' => $this->products[$index]['coin.amount'],
                'text' => 'dummy text'
            ]);
        $product->colors()->attach([1,2,3,7]);
        $product->values()->attach([14,15,19,23]);
        $product->store->categories()->sync([$product->category_id],false);

    }

    public function createSpecial($id)
    {
        $faker = Faker::create('fa_IR');
        $type = ['تومان','عدد'];
        $specType = ['gift','discount','wego_coin'];

        $whichType = $id;
        $text = null;
        if($whichType === 0){
            $text = $faker->paragraph;
        }
        return [
            'type' => $specType[$id],
            'upper_value' => $faker->numberBetween(6000,56000),
            'upper_value_type' => $type[random_int(0,1)],
            'amount' => $faker->numberBetween(10,50),
            'text' => $text
        ];
    }
}
