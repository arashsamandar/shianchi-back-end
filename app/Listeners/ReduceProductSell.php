<?php

namespace App\Listeners;

use App\Events\OrderDeleted;
use App\Product;

class ReduceProductSell
{
    public function handle(OrderDeleted $event)
    {
        $event->orderProducts->each(function ($item) {
            Product::reduceSale($item->product_id, $item->pivot->quantity);
        });
    }
}
