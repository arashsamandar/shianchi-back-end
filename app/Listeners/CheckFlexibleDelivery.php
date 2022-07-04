<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Wego\DeliveryTimeCalculator;

class CheckFlexibleDelivery implements ShouldQueue
{

    /**
     * Handle the event.
     *
     * @param  OrderShipped  $event
     * @return void
     */
    public function handle(OrderShipped $event)
    {
        (new DeliveryTimeCalculator())->calculatePossibilitiesForStep();
    }
}
