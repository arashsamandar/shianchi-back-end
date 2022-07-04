<?php

use App\Wego\Buy\Payment\PaymentFactory;

class PaymentTest extends TestCase
{

    public function testOnlinePayment()
    {

        $order = new \App\Order();
        $order->payment_id = 1;
        $order->id = time();
        $order->final_order_price = 1000;
        $token = "token";
        $userId = 23;
        $url = PaymentFactory::getPayment($order->payment_id)
            ->setOrder($order)
            ->setUserId($userId)
            ->setToken($token)
            ->getUrl();

        $search = env('ONLINE_PAYMENT_URL_DOMAIN') . 'waitingOrder?';
        $urlWithoutDomain = str_replace($search, '', $url);
        $params = explode('&', $urlWithoutDomain);
        list($encryptOrderId, $expectedToken, $encryptUserId, $encryptOrderPrice) = array_map(function ($payload) {
            list(, $encrypt) = explode('=', $payload);
            return $encrypt;
        }, $params);
        $this->assertEquals($order->id, decrypt($encryptOrderId));
        $this->assertEquals($order->final_order_price, decrypt($encryptOrderPrice));
        $this->assertEquals($userId, decrypt($encryptUserId));
        $this->assertEquals($token, $expectedToken);
    }
}
