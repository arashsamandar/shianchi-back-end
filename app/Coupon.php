<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['id', 'amount', 'expiration_time', 'order_id', 'min_purchase', 'type'];
    public $incrementing = false; // <---------
    const AVAILABLE = 'available';
    const EXPIRED = 'expired';
    const USED = 'used';
    const GIFT = 'gift';
    const GAME_GIFT = 'game_gift';
    const COUPON = 'coupon';
    const PERCENT_COUPON = 'percent_coupon';

    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }

    public function setCouponStatus()
    {
        if ($this->type == self::COUPON || $this->type == self::PERCENT_COUPON) {
            $this->status = self::USED;
            $this->save();
        }
    }

    public function rollBackStatus()
    {
        if ($this->status == self::USED) {
            $this->status = self::AVAILABLE;
            $this->save();
        }
    }
}
