<?php

namespace App\Http\Controllers;

use App\BuyerAddress;
use Illuminate\Http\Request;

use App\Http\Requests;
use Wego\Shipping\Shipping;


class ShippingController extends ApiController
{

    public function store(Request $requests){
        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
            // Ignores notices and reports all other kinds... and warnings
            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
            //     error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
        }
        $address = BuyerAddress::findOrFail($requests->address);

        $this->authorize('view', $address);

        return (new Shipping())
            ->setAddress($address)
            ->setQuantities($requests->data)
            ->get();

    }
}
