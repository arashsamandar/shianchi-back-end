<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testOrderWithoutCoupon()
    {
        $x = new \Wego\Buy\Order([['detail_id' => 310, 'quantity' => 1], ['detail_id' => 890, 'quantity' => 15]], collect(['address' => 98, 'shipping_company' => 'Post', 'delivery_time' => '13', 'payment_id' => 2]));
        // 3028800 * 15 - 12 + 55000 * 1 = 45486988
        $order = $x
            ->setUser(\App\User::find(23))
            ->setCouponId(null)
            ->generate();
        $this->assertEquals($order->status, null);
        $this->assertEquals($order->user_id, 23);
        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
        $this->assertEquals($order->gift_id, null);
        $this->assertEquals($order->delivery_time, "13");
        $this->assertEquals($order->address_id, 98);
        $this->assertEquals($order->progressable, 0);
        $this->assertEquals($order->shipping_company, 'Post');
        $this->assertEquals($order->shipping_status, \Wego\Shipping\Price\ShippingPrice::NOT_FREE);
        $this->assertEquals($order->final_products_price, 45487000);
        $this->assertLessThan($order->final_order_price, 45487000);
        $this->assertEquals($order->payment_id, 2);
        $this->assertEquals($order->total_discount, 0);
    }

    public function testOrderWithCoupon()
    {
        sleep(1);
        $x = new \Wego\Buy\Order([['detail_id' => 310, 'quantity' => 1], ['detail_id' => 890, 'quantity' => 15]], collect(['address' => 98, 'shipping_company' => 'Post', 'delivery_time' => '13', 'payment_id' => 2]));
        // 3028800 * 15 - 12 + 55000 * 1 = 45486988 - 10000 (coupon amount)
        $coupon = \App\Coupon::where('status',\App\Coupon::AVAILABLE)->first();
        $order = $x
            ->setUser(\App\User::find(23))
            ->setCouponId($coupon->id)
            ->generate();
        $this->assertEquals($order->status, null);
        $this->assertEquals($order->user_id, 23);
        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
        $this->assertEquals($order->coupon_id, $coupon->id);
        $this->assertEquals($order->delivery_time, "13");
        $this->assertEquals($order->address_id, 98);
        $this->assertEquals($order->progressable, 0);
        $this->assertEquals($order->shipping_company, 'Post');
        $this->assertEquals($order->shipping_status, \Wego\Shipping\Price\ShippingPrice::NOT_FREE);
        $this->assertEquals($order->final_products_price, 45487000);
        $this->assertLessThan($order->final_order_price, 45487000 - $coupon->amount);
        $this->assertEquals($order->payment_id, 2);
        $this->assertEquals($order->total_discount, 0 + $coupon->amount);
    }

    public function testOrderWithExpiredCoupon()
    {
        sleep(1);
        $x = new \Wego\Buy\Order([['detail_id' => 310, 'quantity' => 1], ['detail_id' => 890, 'quantity' => 15]], collect(['address' => 98, 'shipping_company' => 'Post', 'delivery_time' => '13', 'payment_id' => 2]));
        // 3028800 * 15 - 12 + 55000 * 1 = 45486988
        $order = $x
            ->setUser(\App\User::find(23))
            ->setCouponId("0febgi9zOH")
            ->generate();
        $this->assertEquals($order->status, null);
        $this->assertEquals($order->user_id, 23);
        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
        $this->assertEquals($order->gift_id, null);
        $this->assertEquals($order->delivery_time, "13");
        $this->assertEquals($order->address_id, 98);
        $this->assertEquals($order->progressable, 0);
        $this->assertEquals($order->shipping_company, 'Post');
        $this->assertEquals($order->shipping_status, \Wego\Shipping\Price\ShippingPrice::NOT_FREE);
        $this->assertEquals($order->final_products_price, 45487000);
        $this->assertLessThan($order->final_order_price, 45487000);
        $this->assertEquals($order->payment_id, 2);
        $this->assertEquals($order->total_discount, 0);

    }

    public function testOrderWithWrongCoupon()
    {
        sleep(1);
        $x = new \Wego\Buy\Order([['detail_id' => 310, 'quantity' => 1], ['detail_id' => 890, 'quantity' => 15]], collect(['address' => 98, 'shipping_company' => 'Post', 'delivery_time' => '13', 'payment_id' => 2]));
        // 3028800 * 15 - 12 + 55000 * 1 = 45486988
        $order = $x
            ->setUser(\App\User::find(23))
            ->setCouponId("ewgfds")
            ->generate();
        $this->assertEquals($order->status, null);
        $this->assertEquals($order->user_id, 23);
        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
        $this->assertEquals($order->gift_id, null);
        $this->assertEquals($order->delivery_time, "13");
        $this->assertEquals($order->address_id, 98);
        $this->assertEquals($order->progressable, 0);
        $this->assertEquals($order->shipping_company, 'Post');
        $this->assertEquals($order->shipping_status, \Wego\Shipping\Price\ShippingPrice::NOT_FREE);
        $this->assertEquals($order->final_products_price, 45487000);
        $this->assertLessThan($order->final_order_price, 45487000);
        $this->assertEquals($order->payment_id, 2);
        $this->assertEquals($order->total_discount, 0);

    }

    public function testOrderWithOrderId()
    {
        $x = new \Wego\Buy\Order([['detail_id' => 310, 'quantity' => 1], ['detail_id' => 890, 'quantity' => 15]], collect(['address' => 98, 'shipping_company' => 'Post', 'delivery_time' => '13', 'payment_id' => 2]));
        // 3028800 * 15 - 12 + 55000 * 1 = 45486988
        $randomId = rand(1,15000000);
        $order = $x
            ->setUser(\App\User::find(23))
            ->setCouponId(null)
            ->setId($randomId)
            ->generate();

        $this->assertEquals($order->status, \App\Order::IN_PROGRESS);
        $this->assertEquals($order->user_id, 23);
        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
        $this->assertEquals($order->gift_id, null);
        $this->assertEquals($order->delivery_time, "13");
        $this->assertEquals($order->address_id, 98);
        $this->assertEquals($order->progressable, 0);
        $this->assertEquals($order->shipping_company, 'Post');
        $this->assertEquals($order->shipping_status, \Wego\Shipping\Price\ShippingPrice::NOT_FREE);
        $this->assertEquals($order->final_products_price, 45486988);
        $this->assertLessThan($order->final_order_price, 45486988);
        $this->assertEquals($order->payment_id, 2);
        $this->assertEquals($order->total_discount, 12);
        $this->assertEquals($order->id, $randomId);
    }


}
