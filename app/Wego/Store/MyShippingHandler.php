<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 07/05/16
 * Time: 17:56
 */

namespace Wego\Store;

use App\FreeShippingConditions;
use App\SelfShippingStore;
use App\Store;
use Carbon\Carbon;

class MyShippingHandler implements StorePivotHandlerInterface
{

    public function save(array $requests, Store $store)
    {
        //$this->saveSelfShipping($requests,$store);

        $this->saveFreeShipping($requests,$store);
    }

    private function transformSelfShippingToDatabase($request)
    {
        return [
            "delivery_from" => $request["deliveryFrom"],
            "delivery_to" => $request["deliveryTo"],
            "price" => $request["price"],
        ];
    }
    private function transformFreeShippingToDatabase($request)
    {
        return [
            "upper_value" => $request["conditionPrice"],
            "city" => 'تهران'
        ];
    }

    private function saveSelfShipping($request, Store $store)
    {
        $store->self_shipping()->save(new SelfShippingStore($this->transformSelfShippingToDatabase($request)));
    }

    private function saveFreeShipping($request, Store $store)
    {
        $store->free_shipping_condition()->attach($request["type"],$this->transformFreeShippingToDatabase($request));
    }
}