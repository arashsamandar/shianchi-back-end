<?php

namespace App\Events;

use App\Order;
use Illuminate\Queue\SerializesModels;

use Illuminate\Broadcasting\InteractsWithSockets;

class OrderDeleted
{
    use InteractsWithSockets, SerializesModels;

    public $order;
    public $orderProducts;


    public function __construct(Order $order,$orderProducts)
    {
        $this->order = $order;
        $this->orderProducts = $orderProducts;
    }

}
