<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\Exceptions\ExpiredCouponException;
use App\Exceptions\UsedCouponException;
use App\Gift;
use App\Order;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Wego\UserHandle\UserPermission;

class CouponController extends ApiController
{
    public function generateRandomString($length = 4)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function storeCoupon(Request $request)
    {
        //UserPermission::checkStaffPermission();
        $newId = $this->generateRandomString();
        $couponData = $request->all();
        $couponData['id'] = $newId;
        $couponData['type'] = Coupon::COUPON;
        try {
            $coupon = Coupon::create($couponData);
        } catch (QueryException $e) {
            $couponData['id'] = $this->generateRandomString();
            $coupon = Coupon::create($couponData);
        }
        return $this->respondOk($coupon->id,"id");
    }

    public function storePercentCoupon(Request $request)
    {
        //UserPermission::checkStaffPermission();
        $newId = $this->generateRandomString();
        $couponData = $request->all();
        $couponData['id'] = $newId;
        $couponData['type'] = Coupon::PERCENT_COUPON;
        try {
            $coupon = Coupon::create($couponData);
        } catch (QueryException $e) {
            $couponData['id'] = $this->generateRandomString();
            $coupon = Coupon::create($couponData);
        }
        return $this->respondOk($coupon->id,"id");
    }

    public function generateNumberOfCoupons(Request $request)
    {
        $num = $request->count;
        $ids = [];
        for($i=0;$i<$num;$i++){
            $newId = $this->generateRandomString(4);
            $couponData = $request->all();
            $couponData['id'] = $newId;
            try {
                $coupon = Coupon::create($couponData);
            } catch (QueryException $e) {
                $couponData['id'] = $this->generateRandomString(4);
                $coupon = Coupon::create($couponData);
            }
            $ids[] = $coupon->id;
        }
        return $ids;
    }

    public static function setCouponExpirationStatus()
    {
        Coupon::where('expiration_time', '<', Carbon::now())->where('status', '=', Coupon::AVAILABLE)
            ->update(['status' => Coupon::EXPIRED]);
    }


    public function getCoupon($id)
    {
        $order_price = request()->order_price;
        $coupon = Coupon::findOrFail($id);
        if ($coupon->status == Coupon::AVAILABLE) {
            if($coupon->type == Coupon::PERCENT_COUPON){
                $factor = $coupon->amount / 100;
                $amount = $order_price * $factor;
                if ($amount > $coupon->min_purchase) {
                    return ['amount' => $coupon->min_purchase, 'id' => $coupon->id , 'min_purchase'=>0];
                } else {
                    return ['amount' => $amount, 'id' => $coupon->id,"min_purchase"=>0];
                }
            }
            return $coupon->toArray();
        } else if ($coupon->status == Coupon::EXPIRED){
            (new ApiController())->respondWithError('کوپن مورد نظر منقضی شده است');
        } else {
            (new ApiController())->respondWithError('کوپن مورد نظر قبلا استفاده شده است');
        }
    }

    public function changeCouponExpiration()
    {
        Coupon::where('status',Coupon::AVAILABLE)->update(['expiration_time'=>"2017-09-23 00:00:00"]);
        return $this->respondOk();
    }

    public function setAllOldCouponsType()
    {
        Coupon::chunk(200, function ($coupons) {
            foreach ($coupons as $coupon) {
                $coupon->type = Coupon::COUPON;
                $coupon->save();
            }
        });
    }
    public function extendExpirationTime()
    {
        Coupon::where('expiration_time','like',"%2018-03-21%")
            ->update(['expiration_time'=>"2018-04-21 00:00:00"]);
        return $this->respondOk();
    }

}
