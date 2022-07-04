<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ProductAdded
{
    use InteractsWithSockets, SerializesModels;
    public $product;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($product)
    {
        $this->product = $product;
    }
}
