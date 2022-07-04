<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use Illuminate\Contracts\Queue\ShouldQueue;
use Wego\Services\Notification\SmsNotifier;

class SendShipmentSMS implements ShouldQueue
{
    private $smsNotifier;

    public function __construct(SmsNotifier $smsNotifier)
    {
        $this->smsNotifier = $smsNotifier;
    }

    public function handle(OrderShipped $event)
    {
        $this->smsNotifier
            ->setMessage("سفارش " . $event->order->id . " باموفقیت ثبت شد")
            ->setReceiver(env('STAFF_NOTIFIER_NUMBER'))
            ->send();
    }
}
