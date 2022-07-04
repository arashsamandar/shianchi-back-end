<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendShipmentEmail implements ShouldQueue
{



    public function handle(OrderShipped $event)
    {
        Mail::send('email.shipment',['content'=>"سفارش " . $event->order->id . " باموفقیت ثبت شد"],function($message){
            $message->to("support@wegobazaar.com")->subject('ثبت سفارش');
        });
    }
}
