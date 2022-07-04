<?php

namespace App\Listeners;

use App\Events\ProductWatched;
use App\Product;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Recombee\RecommApi\Client;
use Recombee\RecommApi\Requests\AddDetailView;
use Wego\Services\Recommendation\RecombeeRecommender;

class SendDetailViewToRecommender implements ShouldQueue
{
    private $recommender;

    public function __construct(RecombeeRecommender $recombeeRecommender)
    {
        $this->recommender = $recombeeRecommender;
    }

    public function handle(ProductWatched $event)
    {
        $this->recommender->addProductDetailView($event->clientId,$event->productId);
    }
}
