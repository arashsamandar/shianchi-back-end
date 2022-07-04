<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class OrderStatusSetToCanceled
{
    use InteractsWithSockets, SerializesModels;
    public $orderId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

}
