<?php

use App\Product;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ModelTest extends TestCase
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

    public function testByStatus()
    {
        $products = Product::byStatus(Product::PRE_CONFIRMATION);
        foreach ($products as $product){
            $this->assertEquals($product->confirmation_status,Product::PRE_CONFIRMATION);
        }
    }

    public function testByStore()
    {
        $products = Product::byStore(2)->get();
        foreach ($products as $product){
            $this->assertEquals($product->store_id,2);
        }
    }

    public function testByRejectionMessage()
    {
        $products = Product::byRejectionMessage()->get();
        foreach ($products as $product){
            $this->assertTrue(array_key_exists('rejection_messages',$product->toArray()));
        }
    }
}
