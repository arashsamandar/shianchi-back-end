<?php

namespace App\Listeners;

use App\BuyerAddress;
use App\Events\OrderStatusSetToCanceled;
use App\Order;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Wego\Services\Notification\SmsNotifier;

class SendCancelledOrderSms implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    private $smsNotifier;

    public function __construct(SmsNotifier $smsNotifier)
    {
        $this->smsNotifier = $smsNotifier;
    }

    public function handle(OrderStatusSetToCanceled $event)
    {
        $order = Order::find($event->orderId);
        $buyerAddress = $order->address;
        $mobileNum = $buyerAddress->prefix_mobile_number . $buyerAddress->mobile_number;
        $this->smsNotifier
            ->setMessage("ویگوبازار\nضمن عرض پوزش درخواست شما به دلیل عدم تامین کالا توسط تامین کننده، لغو گردید.\n".
            "در صورت تمایل برای مشاهده سایر کالاها به لینک زیر مراجعه فرمایید.\nwww.wegobazaar.com\nبا تشکر")
            ->setReceiver($mobileNum)
            ->send();
    }
}
