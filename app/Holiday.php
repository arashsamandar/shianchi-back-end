<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Wego\DeliveryTimeCalculator;
use Wego\ShamsiCalender\Shamsi;

class Holiday extends Model
{
    protected $fillable = ['holiday','created_at'];

    public static function isHoliday()
    {
        return !is_null(Holiday::where('holiday', Carbon::now()->toDateString())->first());
    }

    public static function checkToday()
    {
        $deliveryTimeCalculator = (new DeliveryTimeCalculator());
        $now = $deliveryTimeCalculator->getWegoJetDate();
        $todayWegojets = Order::where('created_at', 'like', '%' . $now->toDateString() . '%')
            ->where('shipping_company', 'WegoJet')
            ->where('delivery_time', 'like', '%پنج ساعت%')->count();
        $wegojets = Order::where('shipping_company', 'WegoJet')
            ->where('delivery_time', 'like', '%' . Shamsi::convert($now) . '%')->count();
        $count = $todayWegojets + $wegojets ;
        if ($count > 10){
            $holiday = ['holiday'=>$now->toDateString(),'created_at'=>$now->subDay(7)];
            Holiday::create($holiday);
        }
    }

    public static function checkNextAvailableDay()
    {
        $wegobazaar = (new DeliveryTimeCalculator())->calculatePossibilities();
        $wegobazaar = $wegobazaar[0];
        $wegobazaar['time'] = $wegobazaar['time'][0];
        $time = explode('&',$wegobazaar['time']);
        $date = Carbon::parse($time[0]);
        $orderLimit = 10;
        if ($date->dayOfWeek == 4){
            $orderLimit = 0;
        }
        if($date->dayOfWeek == 6){
            $orderLimit = 5;
        }
//        $wegojets = Order::where('shipping_company', 'WegoJet')
//            ->where('delivery_time', 'like', '%' . Shamsi::convert($date) . '%')->count();
        $wegobazaarOrders = Order::where('shipping_company', 'wegobazaar')
            ->where('delivery_time', 'like', '%' . $date->toDateString() . '%')
            ->where('status','<>',Order::CANCELLED)->count();
//        $count = $wegojets + $wegobazaarOrders ;
        $count = $wegobazaarOrders ;
        if ($count >= $orderLimit){
            $holiday = ['holiday'=>$date->toDateString(),'created_at'=>$date->subDay(1)];
            Holiday::create($holiday);
        }
    }
}
