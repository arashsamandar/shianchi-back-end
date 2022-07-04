<?php

namespace App\Listeners;

use App\Events\ProductAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Recombee\RecommApi\Client;
use Recombee\RecommApi\Requests\AddItem;
use Recombee\RecommApi\Requests\SetItemValues;

class AddProductToRecommender implements ShouldQueue
{

    public function handle(ProductAdded $event)
    {
        $client = new Client('wegobazaar', 'UbsqmSduaoy23L8z4dnSWW09piBEfu6W927JJ0O6N6SK2wAqv173wKaWIRecaIrA');
        $client->send(new AddItem($event->product->id));
        $client->send(new SetItemValues($event->product->id, ['item_type' => 'product']));
    }
}
