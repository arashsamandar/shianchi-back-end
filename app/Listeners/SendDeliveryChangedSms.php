<?php

namespace App\Listeners;

use App\BuyerAddress;
use App\Events\DeliveryTimeChanged;
use App\Order;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Wego\Services\Notification\SmsNotifier;

class SendDeliveryChangedSms implements ShouldQueue
{
    public function __construct(SmsNotifier $smsNotifier)
    {
        $this->smsNotifier = $smsNotifier;
    }

    public function handle(DeliveryTimeChanged $event)
    {
        $order = Order::find($event->orderId);
        $buyerAddress = $order->address;
        $mobileNum = $buyerAddress->prefix_mobile_number . $buyerAddress->mobile_number;
        $this->smsNotifier
            ->setMessage("ویگوبازار\nکاربر گرامی با عرض پوزش زمان تحویل سفارش شما به تاریخ".$event->newDeliveryTime.'تغییر یافت.مسئول ارسال قبل از تحویل کالا با شما در تماس خواهد بود.')
            ->setReceiver($mobileNum)
            ->send();
    }
}
