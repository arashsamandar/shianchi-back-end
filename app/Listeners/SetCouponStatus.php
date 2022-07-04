<?php

namespace App\Listeners;

use App\Coupon;
use App\Events\OrderShipped;
use Illuminate\Contracts\Queue\ShouldQueue;

class SetCouponStatus implements ShouldQueue
{
    public function handle($event)
    {
        if ($event->order->hasGift()){
            $coupon = Coupon::find($event->order->coupon_id);
            $coupon->setCouponStatus();
        }
    }
}
