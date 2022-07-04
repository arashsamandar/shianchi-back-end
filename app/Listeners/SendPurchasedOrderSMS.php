<?php

namespace App\Listeners;

use App\Events\OrderStatusSetToPurchased;
use Illuminate\Contracts\Queue\ShouldQueue;
use Wego\Services\Notification\SmsNotifier;

class SendPurchasedOrderSMS implements ShouldQueue
{
    private $smsNotifier;

    public function __construct(SmsNotifier $smsNotifier)
    {
        $this->smsNotifier = $smsNotifier;
    }

    public function handle(OrderStatusSetToPurchased $event)
    {
        $receiverMobileNumber = $event->order['address']['receiver_prefix_mobile_number'].$event->order['address']['receiver_mobile'];
        $this->smsNotifier
            ->setMessage("ویگوبازار\nسفارش شما با کد پیگیری ".$event->order['id']." پردازش شد و در حال آماده سازی برای ارسال است.\nبا تشکر از انتخاب شما\nhttp://shiii.ir")
            ->setReceiver($receiverMobileNumber)
            ->send();
    }
}
