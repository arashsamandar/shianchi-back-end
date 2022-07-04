<?php

namespace App\Listeners;

use App\Events\StoreEdited;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateStoreProductsInElastic implements ShouldQueue
{

    public function handle(StoreEdited $event)
    {
        $event->store->products()->elastic()->get()->addToIndex();
    }
}
