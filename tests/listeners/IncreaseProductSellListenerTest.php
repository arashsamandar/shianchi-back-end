<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class IncreaseProductSellListenerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        //$this->assertTrue(true);
    }

    public function testHandle()
    {
        $sale = \App\Product::find(303)->sale;
        $productDetail = \Mockery::mock(\App\ProductDetail::class);
        $cProduct = collect([$productDetail]);
        $order = \Mockery::mock(\App\Order::class);
        $order->shouldReceive('getAttribute')->with('products')->andReturn($cProduct);
        $productDetail->shouldReceive('getAttribute')->with('sale')->andReturn(2);
        $productDetail->shouldReceive('getAttribute')->with('pivot')->andReturn((object)['quantity' => 3]);
        $productDetail->shouldReceive('getAttribute')->with('product_id')->andReturn(303);
        $listener = new \App\Listeners\IncreaseProductSell();
        $listener->handle(new \App\Events\OrderShipped($order));
        $product = \App\Product::find(303);
        $this->assertEquals($product->sale, $sale + 3);
    }
}
