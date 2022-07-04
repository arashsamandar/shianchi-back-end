<?php

namespace Wego\Buy;

use App\Gift;
use App\Coupon as CouponModel;
use App\Http\Controllers\ApiController;

class Coupon
{
    private $couponId;
    private $priceLimit;
    private $coupon;

    function __construct($couponId, $priceLimit)
    {
        $this->couponId = $couponId;
        $this->priceLimit = $priceLimit;
    }

    public function get()
    {
        return $this->generateGiftOrCoupon()->checkAndGet();
    }

    private function generateGiftOrCoupon()
    {
        $this->coupon = CouponModel::find($this->couponId);
        if(is_null($this->coupon) && !empty($this->couponId)){
            return (new ApiController())->setStatusCode(404)->respondWithError("کوپن وارد شده معتبر نیست");
        }
        return $this;
    }

    private function checkAndGet()
    {
        if (is_null($this->coupon))
            return collect(['amount' => 0, 'id' => null]);
        else if ($this->coupon->status != CouponModel::AVAILABLE)
            return collect(['amount' => 0, 'id' => null]);
        if($this->coupon->type == CouponModel::GAME_GIFT){
            return collect(['amount' => 0, 'id' => null]);
        }
        if ($this->coupon->type == CouponModel::PERCENT_COUPON) {
            $factor = $this->coupon->amount / 100;
            $amount = $this->priceLimit * $factor;
            if ($amount > $this->coupon->min_purchase) {
                return collect(['amount' => $this->coupon->min_purchase, 'id' => $this->coupon->id]);
            } else {
                return collect(['amount' => $amount, 'id' => $this->coupon->id]);
            }
        } else {
            if ($this->coupon->min_purchase <= $this->priceLimit)
                return collect(['amount' => $this->coupon->amount, 'id' => $this->coupon->id]);
            return collect(['amount' => 0, 'id' => null]);
        }
    }

}