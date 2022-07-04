<?php

namespace App\Events;

use App\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class OrderStatusSetToSent
{
    use InteractsWithSockets, SerializesModels;
    public $orderId ;

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
