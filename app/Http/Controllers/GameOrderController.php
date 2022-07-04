<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\GameOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GameOrderController extends ApiController
{
    public function finishGameOperation($id)
    {
        $orderId = decrypt($id);
        GameOrder::where('id',$orderId)->update(['status'=>GameOrder::PAYED]);
    }

    public function getGameOrderById($id)
    {
        $orderId= decrypt($id);
        $order = GameOrder::find($orderId)->toArray();
        return $order;
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $coupon = Coupon::where('id',$request->coupon)->where('type',Coupon::GAME_GIFT)->first();
        if(!is_null($coupon) && $data['payment_type'] == GameOrder::ONLINE){
            $data['price'] = $data['price'] - $coupon->amount;
            $data['coupon_id'] = $coupon->id;
        }
        $data['id'] = time();
        $order = GameOrder::create(array_except($data,['coupon']));
        $url = GameOrder::PaymentUrl($order);
        return $this->respondOk($url, 'path');

    }

    public function index()
    {
        $gameOrders = GameOrder::all();
        return $gameOrders;
    }

    public function checkGameCoupon($id)
    {
        $coupon = Coupon::where('type',Coupon::GAME_GIFT)->where('id',$id)->first();
        if(!is_null($coupon)){
            return $this->respondOk($coupon->amount,"amount");
        }
        return $this->respondOk("0","amount");
    }

    public function getCounter()
    {
        $count  = GameOrder::count();
        $count = $count+19;
        $totalCount = $count;
        return $this->respondOk($totalCount,"count");
    }

    public function storeGameGift(Request $request)
    {
        $giftData['id'] = $request->id;
        $giftData['amount'] = 10000;
        $giftData['min_purchase'] = 0;
        $giftData['expiration_time']= "2017-09-01 00:00:00";
        $giftData['type'] = Coupon::GAME_GIFT;
        $gift = Coupon::create($giftData);
        return $this->respondOk($gift->id,"id");
    }
}
