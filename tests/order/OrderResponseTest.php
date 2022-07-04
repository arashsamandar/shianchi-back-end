<?php

use App\Http\Controllers\OrderController;
use App\Order;
use App\Product;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderResponseTest extends TestCase
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

//    public function testOrderSetStatusToAvailable()
//    {
//        $order = Order::findOrFail(1507095864);
//        $order->setStatus();
//
//        //$this->assertTrue($order->status, Order::)
//
//    }

    public function testAlaki()
    {
        $moj = \App\ProductDetail::whereHas('orders')->with(['orders','product'])->get();

        $moj = Order::with(['products'=>function($query){
            $query->with(['store','product']);
        }])->get();
        dd($moj->toArray());
    }
//    public function testOrderGetStatus()
//    {
//
//        $order = Order::findOrFail(1507095864);
//        $st = $order->getStatus();
//        $this->assertEquals($st, Order::IN_PROGRESS);
//        $order->products()->updateExistingPivot(303, ['status'=>Product::AVAILABLE]);
//        sleep(1);
//
//        $order = Order::findOrFail(1507095864);
//        $st = $order->getStatus();
//        $this->assertEquals($st, Order::IN_PROGRESS);
//
//        $order->products()->updateExistingPivot(53, ['status'=>Product::AVAILABLE]);
//        sleep(1);
//        $order = Order::findOrFail(1507095864);
//        $st = $order->getStatus();
//
//        $this->assertEquals($st, Order::AVAILABLE);
//
//        $order->products()->updateExistingPivot(53, ['status'=>Product::UNAVAILABLE]);
//        sleep(1);
//        $order = Order::findOrFail(1507095864);
//        $st = $order->getStatus();
//        $this->assertEquals($st, Order::UNAVAILABLE);
//
//    }
}
