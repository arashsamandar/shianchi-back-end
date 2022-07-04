<?php

namespace App\Listeners;

use App\Events\CategoriesUpdated;
use Elasticsearch\ClientBuilder;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteProductsElastic implements ShouldQueue
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
        if(!empty($event->deletedCategoriesId)) {
            $param = [
                'type' => 'products', 'index' => 'wego_1',
                'body' => ['query' => ['filtered' => ['filter' => ['terms' => ['category_id' => $event->deletedCategoriesId]]]]]
            ];
            $client = ClientBuilder::create()->build();
            $client->deleteByQuery($param);
        }
    }
}
