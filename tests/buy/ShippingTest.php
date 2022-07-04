<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ShippingTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testShipping()
    {
        $baghali = new \Wego\Shipping\Shipping();
        $address = \App\BuyerAddress::findOrFail(11);

        $baghali->setAddress($address)->setQuantities([['detail_id' => 890, 'quantity' => 1],['detail_id' => 310, 'quantity' => 1]])->get();
    }

    public function testTotalPrice()
    {
        $asghar = new \Wego\Shipping\RequestedProducts([['detail_id' => 890, 'quantity' => 1],['detail_id' => 310, 'quantity' => 1]]);

        $this->assertEquals($asghar->getTotalPrice(), 3083800);
        $this->assertEquals($asghar->getTotalWeight(), 3460);
    }

    public function testTipaxShippingResult()
    {
        $tipax = new \Wego\Shipping\Company\Tipax();
        $address = \App\BuyerAddress::findOrFail(98); // bandar abbas
        $result = $tipax->setAddress($address)->setTotalWeight(1100)->setTotalProductsPrice(5000)->get();
        $this->assertEquals($result['status'],\Wego\Shipping\Price\ShippingPrice::NOT_FREE);
        $this->assertEquals($result['shipping_id'],3);
        $this->assertEquals($result['price'], 16350);
        $this->assertEquals($result['shipping_time'], 'دو تا چهار روز کاری');
        $this->assertEquals($result['company'], 'tipax');
        $this->assertEquals($result['persian_name'], 'تیپاکس');
        $this->assertEquals(count($result['payment']), 2);

        $tipax = new \Wego\Shipping\Company\Post();
        $address = \App\BuyerAddress::findOrFail(11); // bandar abbas
        $result = $tipax->setAddress($address)->setTotalWeight(1100)->setTotalProductsPrice(5000)->get();
        $this->assertEmpty($result);
    }

    public function testPostShippingResult()
    {
        $post = new \Wego\Shipping\Company\Post();
        $address = \App\BuyerAddress::findOrFail(98); // bandar abbas
        $result = $post->setAddress($address)->setTotalWeight(4200)->setTotalProductsPrice(5000)->get();
        $this->assertEquals($result['status'],\Wego\Shipping\Price\ShippingPrice::NOT_FREE);
        $this->assertEquals($result['shipping_id'],2);
        //$this->assertEquals($result['price'], 12154);
        $this->assertEquals($result['shipping_time'], 'دو تا چهار روز کاری');
        $this->assertEquals($result['company'], 'Post');
        $this->assertEquals($result['persian_name'], 'پست');
        $this->assertEquals(count($result['payment']), 1);

        $post = new \Wego\Shipping\Company\Post();
        $address = \App\BuyerAddress::findOrFail(11); // tehran
        $result = $post->setAddress($address)->setTotalWeight(1100)->setTotalProductsPrice(5000)->get();
        $this->assertEmpty($result);

    }

    public function testWegobazaarShippingResult()
    {

        $post = new \Wego\Shipping\Company\Wegobazaar();
        $address = \App\BuyerAddress::findOrFail(98);
        $result = $post->setAddress($address)->setTotalWeight(1100)->setTotalProductsPrice(5000)->get();
        $this->assertEmpty($result);


        $post = new \Wego\Shipping\Company\Wegobazaar();
        $address = \App\BuyerAddress::findOrFail(11);
        $result = $post->setAddress($address)->setTotalWeight(4200)->setTotalProductsPrice(5000)->get();
        $this->assertEquals($result['status'],\Wego\Shipping\Price\ShippingPrice::NOT_FREE);
        $this->assertEquals($result['shipping_id'],1);
        $this->assertEquals($result['price'], 5000);
        $this->assertEquals(count($result['shipping_time']), 3);
        $this->assertEquals($result['company'], 'Wegobazaar');
        $this->assertEquals($result['persian_name'], 'ویگوبازار');
        $this->assertEquals(count($result['payment']), 3);


        $post = new \Wego\Shipping\Company\Wegobazaar();
        $address = \App\BuyerAddress::findOrFail(11);
        $result = $post->setAddress($address)->setTotalWeight(4200)->setTotalProductsPrice(500000)->get();
        $this->assertEquals($result['status'],\Wego\Shipping\Price\ShippingPrice::FREE);
        $this->assertEquals($result['shipping_id'],1);
        $this->assertEquals($result['price'], 5000);
        $this->assertEquals(count($result['shipping_time']), 3);
        $this->assertEquals($result['company'], 'Wegobazaar');
        $this->assertEquals($result['persian_name'], 'ویگوبازار');
        $this->assertEquals(count($result['payment']), 3);
    }

    public function testWegoJetShippingResult()
    {
        $jet = new \Wego\Shipping\Company\WegoJet();
        $address = \App\BuyerAddress::findOrFail(98);
        $result = $jet->setAddress($address)->setTotalWeight(1100)->setTotalProductsPrice(5000)->get();
        $this->assertEmpty($result);


        $jet = new \Wego\Shipping\Company\WegoJet();
        $address = \App\BuyerAddress::findOrFail(11);
        $result = $jet->setAddress($address)->setTotalWeight(4200)->setTotalProductsPrice(5000)->get();
        $this->assertEquals($result['status'],\Wego\Shipping\Price\ShippingPrice::NOT_FREE);
        $this->assertEquals($result['shipping_id'],4);
        $this->assertEquals($result['price'], 10000);
       // $this->assertEquals(count($result['shipping_time']), 3);
        $this->assertEquals($result['company'], 'WegoJet');
        $this->assertEquals($result['persian_name'], 'ویگو جت');
        $this->assertEquals(count($result['payment']), 3);
        dd($result);
    }

    public function testExample()
    {
        $this->assertTrue(true);
    }
}
