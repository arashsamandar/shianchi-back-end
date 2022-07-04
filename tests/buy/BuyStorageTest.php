<?php

use App\Buyer;
use App\User;
use App\WegoCoin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Wego\Buy\BuyProcessTestingUtil;
use Wego\Buy\BuyStorageUtil;

class BuyStorageTest extends TestCase
{
    use DatabaseTransactions;

    protected $order = [
        "orders"=>[
            "store_id"=>11,
            "product_id"=>101,
            "quantity"=>4,
            "address"=>"1",
            "shipping_company_id"=>1,
            "delivery_time"=>"۱۳۹۵/۰۶/۰۹&16-15",
            "payment_id"=>3,
            "color"=>1,
            "specifications"=>[1]
        ]
];
    protected $orderContent = [
        101 =>   [
            "discount" => 60000.0,
            "wego_coin_use" => 22,
            "wego_coin_get" => 198.0,
            "quantity" => 4,
            "gift_count" => 2.0,
            "gift" => "dummy text",
            "price" => 118000.0,
            "buyer_address_id" => "1",
            "product_id" => 101,
            "shipping_id" => 1,
            "payment_id" => 1,
            "delivery_time" => "16-15",
            "delivery_date" => "24/1/1388",
            "store_id" => "11",
            "wego_coins_to_update" =>  [
                0 =>  [
                    "wego_coin_id" => 31,
                    "subtracted_amount" => "4",
                    "remained_amount" => 0,
                    "product_id" => 101,
                ],
                1 =>  [
                    "wego_coin_id" => 32,
                    "subtracted_amount" => "18",
                    "remained_amount" => 0,
                    "product_id" => 101,
                ],
            ],
        ],
//        102 =>   [
//            "discount" => 0,
//            "wego_coin_use" => 0,
//            "wego_coin_get" => 0,
//            "quantity" => 2,
//            "gift_count" => 0.0,
//            "gift" => "dummy text",
//            "price" => 110000,
//            "buyer_address_id" => "1",
//            "product_id" => 102,
//            "shipping_id" => 1,
//            "payment_id" => 1,
//            "delivery_time" => "16-15",
//            "delivery_date" => "24/1/1388",
//            "store_id" => "11",
//            "wego_coins_to_update" =>  [
//                0 =>  [
//                    "wego_coin_id" => 31,
//                    "subtracted_amount" => "4",
//                    "remained_amount" => 0,
//                    "product_id" => 101,
//                ],
//                1 =>  [
//                    "wego_coin_id" => 32,
//                    "subtracted_amount" => "18",
//                    "remained_amount" => 0,
//                    "product_id" => 101,
//                ],
//            ],
//        ],
//        103 =>   [
//            "discount" => 0,
//            "wego_coin_use" => 13,
//            "wego_coin_get" => 27.0,
//            "quantity" => 7,
//            "gift_count" => 7.0,
//            "gift" => "dummy text",
//            "price" => 15000,
//            "buyer_address_id" => "1",
//            "product_id" => 103,
//            "shipping_id" => 1,
//            "payment_id" => 1,
//            "delivery_time" => "16-15",
//            "delivery_date" => "24/2/1388",
//            "store_id" => "12",
//            "wego_coins_to_update" => [
//                0 =>  [
//                    "wego_coin_id" => 31,
//                    "subtracted_amount" => "4",
//                    "remained_amount" => 0,
//                    "product_id" => 101,
//                ],
//                1 =>  [
//                    "wego_coin_id" => 32,
//                    "subtracted_amount" => "18",
//                    "remained_amount" => 0,
//                    "product_id" => 101,
//                ],
//                2 =>  [
//                    "wego_coin_id" => 33,
//                    "subtracted_amount" => "13",
//                    "remained_amount" => 0,
//                    "product_id" => 103,
//                ],
//            ],
//        ],
    ];

    public function testQueryForAddOrderToElasticSearch(){
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjIzLCJpc3MiOiJodHRwOlwvXC93ZWdvLmRldlwvYXBpXC92MVwvYXV0aGVudGljYXRlIiwiaWF0IjoxNDgxNjM3NTYwLCJleHAiOjE0ODUyMzc1NjAsIm5iZiI6MTQ4MTYzNzU2MCwianRpIjoiOGJkN2VlZjEyMDM4OTAyMTI3N2ZiZWZkNTVkOWVjMjcifQ.qYDMeOwyJTl-MQ5k-o2nRbrRLx4_l63VVX90adQKCNE";
        $this->refreshApplication();
        $this->action( 'POST','OrderController@store',['token'=>$token],$this->order,[],[]);

        dump('finish');

    }

    public function testWegoCoinUpdate()
    {
        $user = User::where('email','=','buyer@1.com')->first();
        $copyContent = $this->orderContent;
        $buyStorage = new BuyStorageUtil($user,$copyContent);
        $buyStorage->updateWegoCoins();
        $wegoCoin = WegoCoin::where('id','=',32)->first();
        $this->assertEquals(0,$wegoCoin->amount);
        $wegoCoin = WegoCoin::where('id','=',31)->first();
        $this->assertEquals(0,$wegoCoin->amount);
    }

    public function testUserWegoCoins(){
        $user = User::create(['email'=>'doolab@1.com','name'=>'hasan','pass'=>12]);
        $buyStorageUtil = new BuyStorageUtil($user,$this->orderContent);
        $buyStorageUtil->addUserWegoCoins();
        $wegoCoins = $user->wegoCoin()->get();
        $this->assertEquals(2,count($wegoCoins));
        $this->seeInDatabase('wego_coins',['user_id'=>$user->id,'amount' => 27]);
        $this->seeInDatabase('wego_coins',['user_id'=>$user->id,'amount' => 198]);
    }

    public static function createBuyer(){
        $buyer = Buyer::create([
            "last_name" => "hoseini",
            "national_code" => "9559359598",
            "mobile_number" => "09124247487",
            "landline_number" => "02177012772",
            "address" => "amirabad amirabad amirabad amirabad amirabad",
            "image_path" => "alaki",
            "company_name" => "tesla motors",
            "card_number" => "1234-1234-1234-1234",
        ]);
        $buyer->user()->create(['name'=>'buyer','email'=>'buyer@2.com','password'=>bcrypt(12)]);
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
        return $buyer;
    }
}