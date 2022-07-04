<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ProductSetToExist
{
    use InteractsWithSockets, SerializesModels;
    public $productId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($productId)
    {
        $this->productId = $productId;
    }

}
