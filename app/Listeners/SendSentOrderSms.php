<?php

namespace App\Listeners;

use App\BuyerAddress;
use App\Events\OrderStatusSetToSent;
use App\Order;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Wego\Services\Notification\SmsNotifier;

class SendSentOrderSms implements ShouldQueue
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

    public function handle(OrderStatusSetToSent $event)
    {
        $order = Order::find($event->orderId);
        $addressId = $order->address_id;
        $buyerAddress = BuyerAddress::find($addressId);
        $mobileNum = $buyerAddress->prefix_mobile_number . $buyerAddress->mobile_number;
        $this->smsNotifier
            ->setMessage("ویگوبازار\nسفارش شما با کد پیگیری ".$event->orderId." به مسئول ارسال تحویل شد.\n".
            "مشاهده کالاهای بیشتر:\nwww.wegobazaar.com\nبا تشکر از خرید شما")
            ->setReceiver($mobileNum)
            ->send();
    }
}
