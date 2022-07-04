<?php

namespace App\Listeners;

use App\Events\ProductWatched;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Wego\ProductViewCount;

class AddIpToElasticSearch implements ShouldQueue
{
    public function handle(ProductWatched $event)
    {
        ProductViewCount::addIpToProductViewIps($event->ip, $event->productId);
    }
}
