<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\ProductDetail;
use Illuminate\Http\Request;
use Wego\Cart\GenerateCart;


class CartController extends ApiController
{

    /**
     * @param Request $request
     * @return mixed
     * return product information if user don't be login
     */
    public function index(Request $request)
    {
        $cart = new GenerateCart();
        $detailIds = $request->product_details;
        $details = ProductDetail::whereIn('id',$detailIds)->get();
        $productIds = $details->pluck('product_id')->toArray();
        $cart->setProductId($productIds)->setDetails($details);
        return $this->respondArray($cart->handle()->getFinalResult());
    }
}
