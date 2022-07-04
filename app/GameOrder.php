<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GameOrder extends Model
{
    const PAYED = 1;
    const NOT_PAYED = 0;
    CONST ONLINE = 1;
    CONST NOT_ONLINE = 2;
    protected $fillable =['name','mobile_number','email','type','game_date','status',
        'alias_name','coupon_id','payment_type','price','id'];

    public static function PaymentUrl($order)
    {
        if($order->payment_type == GameOrder::ONLINE)
            return  env('ONLINE_PAYMENT_URL_DOMAIN').'waitingGameOrder?oi='.encrypt($order->id).
                    '&mo='.encrypt($order->mobile_number).
                    '&op='.encrypt($order->price);
        else
            return
                env('RETURN_FROM_BANK_URL_DOMAIN').
                'checkout?orderId=' . $order->id .
                '&paymentMethod=' . GameOrder::NOT_ONLINE .
                '&transaction_number=' . 0 .
                '&transaction_result=' . 'null' .
                '&date=' . Carbon::now()->toDateString() .
                '&price=' . $order->price .
                '&url=' . ''
                ;
    }
}
