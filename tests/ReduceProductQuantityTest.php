<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReduceProductQuantityTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testProductToZero()
    {

        $productId = 303;
        $product = \App\Product::findOrFail($productId);
        $this->assertNotEquals($product->quantity,0);
        \App\Product::setToZeroQuantity($productId);
        sleep(1);
        $product = \App\Product::findOrFail($productId);
        $this->assertEquals($product->quantity,0);
    }


    public function testReduceProductQuantity(){
        assertTrue(true);
        $productId = 15;
        $reduced = 5;
        $product = \App\Product::findOrFail($productId);
        $currentQuantity = $product->quantity;
        $this->assertGreaterThan($reduced,$product->quantity);
        \App\Product::ReduceQuantity($productId,$reduced);
        sleep(1);
        $product = \App\Product::findOrFail($productId);
        $this->assertEquals($product->quantity,$currentQuantity-$reduced);
    }
}
