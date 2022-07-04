<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
//use Wego\Invoice\OrderInvoice;

class OrderInvoiceTest extends TestCase
{
    const INVOICE_ID = 86;
    public function testOrderInvoiceContent()
    {
        $orderInvoiceGetter = new OrderInvoice();
        $orderInvoice = $orderInvoiceGetter->getOrderInvoice(self::INVOICE_ID);
        $this->checkOrderInvoiceTitle($orderInvoice['order_detail']);
        $this->checkOrderInvoiceStores($orderInvoice['stores_detail']);
        $this->assertTrue(isset($orderInvoice[''])," is not defined");
        $this->assertTrue(isset($orderInvoice[''])," is not defined");
        $this->assertTrue(isset($orderInvoice[''])," is not defined");
        $this->assertTrue(isset($orderInvoice[''])," is not defined");
    }

    public function checkOrderInvoiceTitle($orderInvoiceTitle){
        $this->assertTrue(isset($orderInvoiceTitle['created_at']),"created_at is not defined");
        $this->assertTrue(isset($orderInvoiceTitle['delivery_time']),"delivery_time is not defined");
        $this->assertTrue(isset($orderInvoiceTitle['delivery_date']),"delivery_date is not defined");
        $this->assertTrue(isset($orderInvoiceTitle['id']),"id is not defined");
        $this->assertTrue(isset($orderInvoiceTitle['tracking_number']),"tracking_number is not defined");
        $this->assertTrue(isset($orderInvoiceTitle['products_final_price']),"products_final_price is not defined");
        $this->assertTrue(isset($orderInvoiceTitle['shipping_price']),"shipping_price is not defined");
        $this->assertTrue(isset($orderInvoiceTitle['order_price']),"order_price is not defined");
        $this->assertTrue(isset($orderInvoiceTitle['payment_method_id']),"payment_method_id is not defined");
        $this->assertTrue(isset($orderInvoiceTitle['receiver_name']),"receiver_name is not defined");
        $this->assertTrue(isset($orderInvoiceTitle['receiver_mobile']),"receiver_mobile is not defined");
        $this->assertTrue(isset($orderInvoiceTitle['receiver_phone']),"receiver_phone is not defined");
        $this->assertTrue(isset($orderInvoiceTitle['receiver_address']),"receiver_address is not defined");
    }

    private function checkOrderInvoiceStores($stores){
        foreach ($stores as $store) {
//            $this->checkStoreProducts
        }

    }
}
