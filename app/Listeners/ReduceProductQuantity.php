<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\ProductDetail;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReduceProductQuantity
{

    public function handle($event)
    {
        $orderProducts = $event->order->products;
        $orderProducts->each(function ($item) {
            ProductDetail::reduceQuantity($item->id, $item->pivot->quantity);
        });

    }
}
