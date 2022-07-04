<?php

namespace App\Listeners;

use App\Events\CategoriesUpdated;
use App\Product;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateProductsElastic implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CategoriesUpdated  $event
     * @return void
     */
    public function handle(CategoriesUpdated $event)
    {
        Product::whereIn('category_id',$event->updatedCategoriesId)->elastic()->addToIndex();
    }
}
