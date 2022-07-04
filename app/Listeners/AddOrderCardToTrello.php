<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Events\OrderStatusSetToPurchased;
use App\Jobs\AddOrderTrelloCard;
use Carbon\Carbon;

class AddOrderCardToTrello
{
    public function handle(OrderShipped $event)
    {
        $job = (new AddOrderTrelloCard($event->order->id))->delay(Carbon::now()->addMinutes(5));
        dispatch($job);
    }
}
