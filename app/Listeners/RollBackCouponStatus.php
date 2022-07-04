<?php

namespace App\Listeners;

use App\Coupon;
use App\Events\OrderDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;


class RollBackCouponStatus
{
    public function handle(OrderDeleted $event)
    {
        if ($event->order->hasGift()){
            $coupon = Coupon::find($event->order->coupon_id);
            $coupon->rollBackStatus();
        }
    }
}
