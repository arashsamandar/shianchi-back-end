<?php

namespace App\Listeners;

use App\Events\OrderDeleted;
use App\ProductDetail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class IncreaseProductQuantity
{

    public function handle(OrderDeleted $event)
    {
        $event->orderProducts->each(function ($item) {
            ProductDetail::increaseQuantity($item->id, $item->pivot->quantity);
        });
    }
}
