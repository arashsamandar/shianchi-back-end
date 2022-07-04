<?php

namespace App\Listeners;

use App\Events\NonExistingProductOrderSubmitted;
use App\OutsideOrder;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Wego\Services\Notification\SlackNotifier;

class SendNonExistingOrderSlackMessage implements ShouldQueue
{
    public $slackNotifier;
    public function __construct(SlackNotifier $slackNotifier)
    {
        $this->slackNotifier = $slackNotifier;
    }

    /**
     * Handle the event.
     *
     * @param  OutsideOrderSubmitted  $event
     * @return void
     */
    public function handle(NonExistingProductOrderSubmitted $event)
    {
        $count = OutsideOrder::count();
        $addition = '0';
        if($count % 2 == 1){
            $addition = '1';
        }
        $this->slackNotifier
            ->setMessage("سفارش از کالای ناموجود-".$addition."\nنام: ".$event->outsideOrder->name.
                "\nشماره تماس: ".$event->outsideOrder->phone_number."\nلینک کالا: ".$event->outsideOrder->link.
                "\nتوضیحات: ".$event->outsideOrder->description
            )
            ->setReceiver('@sinapechaz1993')
            ->send();
        $this->slackNotifier
            ->setMessage("سفارش از کالای ناموجود-".$addition."\nنام: ".$event->outsideOrder->name.
                "\nشماره تماس: ".$event->outsideOrder->phone_number."\nلینک کالا: ".$event->outsideOrder->link.
                "\nتوضیحات: ".$event->outsideOrder->description
            )
            ->setReceiver('#content')
            ->send();
    }
}
