<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\OrderShipped' => [
            'App\Listeners\SendShipmentSMSToBuyer',
//            'App\Listeners\SendShipmentSlackMessage',
            'App\Listeners\AddOrderCardToTrello',
            'App\Listeners\ReduceProductQuantity',
            'App\Listeners\IncreaseProductSell',
            'App\Listeners\SetCouponStatus',
            'App\Listeners\CheckFlexibleDelivery'
//            'App\Listeners\SendOrderToRelatedStores'
        ],
        'App\Events\OrderUpdated' => [
            'App\Listeners\ReduceProductQuantity',
            'App\Listeners\IncreaseProductSell',
            'App\Listeners\SetCouponStatus'
        ],
        'App\Events\OrderDeleted' => [
            'App\Listeners\ReduceProductSell',
            'App\Listeners\IncreaseProductQuantity',
            //'App\Listeners\RollBackCouponStatus',
        ],
        'App\Events\OrderStatusSetToPurchased' => [
            'App\Listeners\SendOnlinePaymentLinkViaSMS',
            'App\Listeners\SendOnlinePaymentLinkViaEmail'
        ],
        'App\Events\OrderStatusSetToSent' => [
            'App\Listeners\SendSentOrderSms',
        ],
        'App\Events\OrderStatusSetToCanceled' => [
            'App\Listeners\SendCancelledOrderSms',
        ],
        'App\Events\ProductAdded' => [
            'App\Listeners\AddRandomProductScore',
            'App\Listeners\AddProductToRecommender',
        ],
        'App\Events\StoreEdited' => [
            'App\Listeners\UpdateStoreProductsInElastic'
        ],
        'App\Events\ProductWatched' => [
            'App\Listeners\AddIpToElasticSearch',
//            'App\Listeners\SendDetailViewToRecommender'
        ],
        'App\Events\OutsideOrderSubmitted' => [
//            'App\Listeners\SendOutsideOrderSlackMessage',
            'App\Listeners\AddOutsideOrderTrelloCard',
        ],
        'App\Events\NonExistingProductOrderSubmitted' => [
//            'App\Listeners\SendNonExistingOrderSlackMessage',
            'App\Listeners\AddNonExistingOrderTrelloCard',
        ],
        'App\Events\CategoriesUpdated' => [
            'App\Listeners\UpdateProductsElastic',
            'App\Listeners\DeleteProductsElastic'
        ],
        'App\Events\DeliveryTimeChanged' => [
            'App\Listeners\SendDeliveryChangedSms'
        ],
        'App\Events\ProductSetToExist' => [
            'App\Listeners\checkForInformSms'
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
