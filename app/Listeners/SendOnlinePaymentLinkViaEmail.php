<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Events\OrderStatusSetToPurchased;
use App\Jobs\SendPaymentEmail;
use App\Payment;
use Carbon\Carbon;

class SendOnlinePaymentLinkViaEmail
{
    public function handle(OrderStatusSetToPurchased $event)
    {
        $job = (new SendPaymentEmail($event->order))->delay(Carbon::now()->addMinutes(2));
        dispatch($job);
    }
}
