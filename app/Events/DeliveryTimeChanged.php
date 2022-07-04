<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Wego\DeliveryTimeCalculator;
use Wego\ShamsiCalender\Shamsi;

class DeliveryTimeChanged
{
    use InteractsWithSockets, SerializesModels;
    public $newDeliveryTime;
    public $orderId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($newDeliveryTime,$orderId)
    {
        if (strpos($newDeliveryTime,"حداکثر") !== false){
            $date = (new DeliveryTimeCalculator())->getWegoJetDate();
            $tarikh = Shamsi::convert($date);
        } elseif(strpos($newDeliveryTime,"&") !== false){
            $time = explode('&',$newDeliveryTime);
            $tarikh = Shamsi::convert(Carbon::parse($time[0]));
        }
        $str = substr($tarikh,9,strlen($tarikh)-4);
        $this->newDeliveryTime = $str;
        $this->orderId = $orderId;
    }
}
