<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Wego\Services\Notification\SlackNotifier;

class SendShipmentSlackMessage implements ShouldQueue
{

    public $slackNotifier;
    public function __construct(SlackNotifier $slackNotifier)
    {
        $this->slackNotifier = $slackNotifier;
    }

    public function handle(OrderShipped $event)
    {
        if ($event->order->shipping_company == 'WegoJet'){
            $count = Order::where('created_at','<=',$event->order->created_at)
                ->where('shipping_company','WegoJet')->count();
        } else {
            $count = Order::where('created_at','<=',$event->order->created_at)
                ->where('shipping_company','<>','WegoJet')->count();
        }
        $addition = '0';
        if($count % 2 == 1){
            $addition = '1';
        }
        $slackMessage = "سفارش " . $event->order->id."-".$addition. " باموفقیت ثبت شد\n";
        $slackMessage .= 'نام خریدار: '.$event->order->address->receiver_first_name . ' ' .
            $event->order->address->receiver_last_name."\n";
        foreach ($event->order->products as $productDetail) {
            $slackMessage .= $productDetail->product->persian_name."\n";
        }
        $slackMessage .= "مبلغ نهایی پرداختی: " . $event->order->final_order_price;
        $slackMessage .= "\nسرویس ارسال : ".$event->order->shipping_company;
        $this->slackNotifier
            ->setMessage($slackMessage)
            ->setReceiver('#content')
            ->send();
    }
}
