<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Wego\Buy\OrderProduct\OrderContentCreator;

class OrderContentCreatorTest extends TestCase
{
    use DatabaseTransactions;
    private $requestedProducts = [
        [
            "store_id" =>  11,
            "product_id" =>  101,
            "quantity" =>  4,
            "address" =>  "1",
            "shipping_company_id" => 1,
            "delivery_time" => "24/1/1388&16-15",
            "payment_id" => 1,"color" => 1
        ]
        ,
        [
            "store_id" =>  11,
            "product_id" =>  102,
            "quantity" =>  2,
            "address" =>  "1",
            "shipping_company_id" => 1,
            "delivery_time" => "24/1/1388&16-15",
            "payment_id" => 1,"color" => 1
        ],
        [
            "store_id" =>  12,
            "product_id" =>  103,
            "quantity" =>  7,
            "address" => "1",
            "shipping_company_id" => 1,
            "delivery_time" => "24/2/1388&16-15",
            "payment_id" => 1,"color" => 1
        ]
    ];
    private $userIds = [
        0 => 23,
    ];

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGenerateSpecialOffer(){
        $orderContentCreator = new OrderContentCreator();
        $user = BuyStorageTest::createBuyer()->user;
        $result = $orderContentCreator->create($this->requestedProducts,$user->id);
        $this->assertEquals(60000,$result[101]['discount']);
        $this->assertEquals(22,$result[101]['wego_coin_use']);
        $this->assertEquals(2,$result[101]['gift_count']);
        $this->assertEquals(101,$result[101]['product_id']);
        $this->assertEquals(118000,$result[101]['price']);
        $this->assertEquals(198,$result[101]['wego_coin_get']);

        $this->assertEquals(0,$result[102]['discount']);
        $this->assertEquals(0,$result[102]['wego_coin_use']);
        $this->assertEquals(0,$result[102]['wego_coin_get']);
        $this->assertEquals(2,$result[102]['gift_count']);
        $this->assertEquals(110000,$result[102]['price']);
        $this->assertEquals(102,$result[102]['product_id']);

        $this->assertEquals(0,$result[103]['discount']);
        $this->assertEquals(13,$result[103]['wego_coin_use']);
        $this->assertEquals(27,$result[103]['wego_coin_get']);
        $this->assertEquals(7,$result[103]['gift_count']);
        $this->assertEquals(15000,$result[103]['price']);
        $this->assertEquals(103,$result[103]['product_id']);
    }

    public function testOrderContent(){
        $orderContentCreator = new OrderContentCreator();
        $orderContent = $orderContentCreator->create($this->requestedProducts,$this->userIds[0]);

        foreach ($orderContent as $productOrderContent) {
            $this->assertTrue(isset($productOrderContent['product_english_name']),'product_english_name is not set');
            $this->assertTrue(isset($productOrderContent['product_persian_name']),'product_persian_name is not set');
            $this->assertTrue(isset($productOrderContent['store_english_name']),'store_name is not set');
            $this->assertTrue(isset($productOrderContent['store_persian_name']),'store_persian_name is not set');
            $this->assertTrue(isset($productOrderContent['shipping_price']),'shipping is not set');
            $this->assertTrue(isset($productOrderContent['shipping_status']),'shipping_status is not set');
            $this->assertTrue(isset($productOrderContent['shipping_company']),'shipping_company is not set');
            $this->assertTrue(isset($productOrderContent['address']),'address is not set');
            $this->assertTrue(isset($productOrderContent['province_id']),'province_id is not set');
            $this->assertTrue(isset($productOrderContent['city_id']),'city_id is not set');
            $this->assertTrue(isset($productOrderContent['delivery_date']),'delivery_date is not set');
            $this->assertTrue(isset($productOrderContent['delivery_time']),'delivery_time is not set');
            $this->assertTrue(isset($productOrderContent['receiver_first_name']),'receiver_first_name is not set');
            $this->assertTrue(isset($productOrderContent['receiver_last_name']),'receiver_last_name is not set');
//            $this->assertTrue(isset($productOrderContent['order_id'])f,'order_id is not set');
        }
    }
}