<?php

namespace App\Http\Controllers;


use App\Coupon;
use App\GameScore;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GameScoreController extends Controller
{
    protected $minPurchase;

    public function storeGameScore(Request $request)
    {
//        if (strpos($request->header('Referer'), 'http://shiii.ir') === false) {
//            throw new AccessDeniedHttpException;
//        }
        GameScore::create($request->all());
        $score = $request->input('score');
        $id = (new CouponController())->generateRandomString();
        $couponAmount = $this->getCouponAmountByScore($score);
        $minPurchase = $this->minPurchase;
        $expiration_time = Carbon::now()->addMonth(1)->toDateString();
        return Coupon::create(['id' => $id, 'amount' => $couponAmount, 'min_purchase' => $minPurchase, 'expiration_time' => $expiration_time]);
    }

    private function getCouponAmountByScore($score)
    {
        if ($score > 3 and $score < 8) {
            $this->minPurchase = 20000;
            return 1000;
        } elseif ($score > 7 and $score < 20) {
            $this->minPurchase = 50000;
            return 5000;
        } elseif ($score > 19) {
            $this->minPurchase = 90000;
            return 10000;
        }
    }

    public function getRanking()
    {
        return GameScore::orderBy('score', 'desc')->where('created_at','>',Carbon::today())->get()->take(10);
    }
}
