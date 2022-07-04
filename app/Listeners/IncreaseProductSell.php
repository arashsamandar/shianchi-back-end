<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Product;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class IncreaseProductSell implements ShouldQueue
{

    public function handle($event)
    {
        $orderProducts = $event->order->products;
        $orderProducts->each(function ($item) {
            Product::increaseSale($item->product_id, $item->pivot->quantity);
        });
    }
}
