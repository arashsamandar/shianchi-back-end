<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ProductWatched
{
    use InteractsWithSockets, SerializesModels;
    public $productId,$clientId,$ip;

    /**
     * Create a new event instance.
     *
     * @param $productId
     * @param null $clientId
     * @param null $ip
     */
    public function __construct($productId,$clientId =null,$ip=null)
    {
        $this->productId = $productId;
        $this->clientId = $clientId;
        $this->ip = $ip;
    }
}