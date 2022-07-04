<?php

namespace App\Http\Controllers;

use App\Events\NonExistingProductOrderSubmitted;
use App\Events\OutsideOrderSubmitted;
use App\Http\Requests\OutsideOrderRequest;
use App\OutsideOrder;
use Illuminate\Http\Request;

class OutsideOrderController extends ApiController
{
    public function storeOrder(OutsideOrderRequest $request)
    {
        $orderInfo = $request->all();
        $outsideOrder = OutsideOrder::create($orderInfo);
        event(new OutsideOrderSubmitted($outsideOrder));
        return $this->respondOk();
    }

    public function submitOrderForNonExistingProducts(OutsideOrderRequest $request)
    {
        $orderInfo = $request->all();
        $outsideOrder = OutsideOrder::create($orderInfo);
        event(new NonExistingProductOrderSubmitted($outsideOrder));
        return $this->respondOk();
    }
}
