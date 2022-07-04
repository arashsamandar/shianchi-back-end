<?php

namespace App\Http\Controllers;

use App\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GiftController extends ApiController
{
    public function store(Request $request)
    {
        $giftData = $request->all();
        $giftData['type'] = Coupon::GIFT;
        $gift = Coupon::create($giftData);
        return $this->respondOk($gift->id,"id");
    }
}
