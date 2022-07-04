<?php

namespace App\Listeners;

use App\Events\OrderStatusSetToPurchased;
use App\Jobs\SendPaymentSms;
use App\Payment;
use Carbon\Carbon;

class SendOnlinePaymentLinkViaSMS
{
    public function handle(OrderStatusSetToPurchased $event)
    {
        if ($event->order->payment_id != Payment::ONLINE || $event->order->progressable == 'false') {
            $job = (new SendPaymentSms($event->order))->delay(Carbon::now()->addMinutes(1));
            dispatch($job);
        }
    }
}