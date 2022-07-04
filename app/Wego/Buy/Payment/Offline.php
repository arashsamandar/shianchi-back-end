<?php
/**
 * Created by PhpStorm.
 * User: hoseinz3
 * Date: 9/30/2017 AD
 * Time: 15:42
 */

namespace App\Wego\Buy\Payment;


use Carbon\Carbon;

class Offline extends AbstractPayment
{

    public function getUrl()
    {
        return
            env('RETURN_FROM_BANK_URL_DOMAIN').
            'checkout?orderId=' . $this->order->id .
            '&paymentMethod=' . $this->order->payment_id .
            '&transaction_number=' . 0 .
            '&transaction_result=' . 'null' .
            '&date=' . Carbon::now()->toDateString() .
            '&price=' . $this->order->final_order_price .
            '&url=' . ''
        ;
    }


}