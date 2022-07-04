<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 9/26/17
 * Time: 5:37 PM
 */

namespace Wego\Shipping\Company;


use App\Holiday;
use App\Order;
use App\Payment;
use Carbon\Carbon;
use Wego\DeliveryTimeCalculator;
use Wego\ShamsiCalender\Shamsi;
use Wego\Shipping\Price\ShippingPrice;

class WegoJet extends AbstractShipping
{
    const WEGO_JET_MIN_SHIPPING_PRICE = 20000;
    const WEGO_JET_MAX_SHIPPING_PRICE = 20000;
    protected $persianName = 'ارسال فوری';
    protected $company = 'WegoJet';
    protected $id = 4;
    protected $payment = [
//        [
//            'key' => 'پرداخت نقدی در محل', 'status' => true, 'id' => Payment::CASH
//        ],
        [
            'key' => 'پرداخت کارتی در محل', 'status' => true, 'id' => Payment::CARD
        ],
        [
            'key' => 'پرداخت آنلاین', 'status' => true, 'id' => Payment::ONLINE
        ]
    ];


    protected function price()
    {
        if ($this->totalWeight <= 10000)
            $this->shippingPrice = self::WEGO_JET_MAX_SHIPPING_PRICE;
        elseif ($this->totalWeight <= 20000)
            $this->shippingPrice = 30000;
        elseif ($this->totalWeight <= 30000)
            $this->shippingPrice = 40000;
        elseif ($this->totalWeight <= 40000)
            $this->shippingPrice = 50000;
        else
            $this->shippingPrice = 60000;
        return $this;
    }

    protected function time()
    {
//        $shippingDate = Carbon::now();
//        if(strtotime($shippingDate->toTimeString()) <= strtotime(Order::PURCHASE_START_TIME) && strtotime($shippingDate->toTimeString()) >= strtotime("10:00:00")){
//            $this->shippingTime = "تا سه ساعت بعد از ثبت سفارش";
//        }
//        elseif(strtotime($shippingDate->toTimeString()) >= strtotime(Order::PURCHASE_START_TIME)){
//            $shippingDate = $shippingDate->addDay();
//            $this->shippingTime = 'حداکثر تا ساعت 13 روز '.Shamsi::timeDetail($shippingDate)['weekday'].' مورخ '.Shamsi::convert($shippingDate);
//        }
//        elseif(strtotime($shippingDate->toTimeString()) <= strtotime("10:00:00")){
//            $this->shippingTime = 'حداکثر تا ساعت 13 روز '.Shamsi::timeDetail($shippingDate)['weekday'].' مورخ '.Shamsi::convert($shippingDate);
//        }
//
//        return $this;
        $date = Carbon::now();
        $timeCalculator = new DeliveryTimeCalculator();
        if ($date->dayOfWeek == 4) {
            $timeLimit = "12:00:00";
        } else {
            $timeLimit = Order::PURCHASE_START_TIME;
        }
        if (strtotime($date->toTimeString()) <= strtotime($timeLimit) && strtotime($date->toTimeString()) >= strtotime("10:00:00")) {
            if ($timeCalculator->isImpossibleDay($date) || $timeCalculator->isWegojetHoliday($date)) {
                $date = $date->addDay();
                $date = $timeCalculator->skipWegojetImpossibleDays($date);
                $this->shippingTime = 'حداکثر تا ساعت 18 روز ' . Shamsi::timeDetail($date)['weekday'] . ' مورخ ' . Shamsi::convert($date);
            } else {
                $this->shippingTime = "حداکثر تا هفت ساعت بعد از ثبت سفارش";
            }
        } elseif (strtotime($date->toTimeString()) >= strtotime($timeLimit)) {
            $date = $date->addDay()->subHours(3)->subMinutes(30);
            $date = $timeCalculator->skipWegojetImpossibleDays($date);
            $this->shippingTime = 'حداکثر تا ساعت 18 روز ' . Shamsi::timeDetail($date)['weekday'] . ' مورخ ' . Shamsi::convert($date);
        } elseif (strtotime($date->toTimeString()) <= strtotime("10:00:00")) {
            $date = $timeCalculator->skipWegojetImpossibleDays($date);
            $this->shippingTime = 'حداکثر تا ساعت 18 روز ' . Shamsi::timeDetail($date)['weekday'] . ' مورخ ' . Shamsi::convert($date);
        }
        return $this;
    }

    protected function check()
    {
        $this->turn = true;
//        $date = Carbon::now();
//        if ($date->dayOfWeek == 5)
//            $this->turn = false;
//
//        elseif (Holiday::isHoliday())
//            $this->turn = false;
//
//        elseif ($date->toTimeString() > Order::PURCHASE_START_TIME)
//        {
//            $date = $date->addDay(1);
//            if ($date->dayOfWeek == 5) {
//                $this->turn = false;
//            }
//            elseif (Holiday::isHoliday())
//            {
//                $this->turn = false;
//            }
//        }

        $this->turn = ($this->address->hasWegoShipping()) && $this->turn;
        return $this;
    }

    protected function freeShipping()
    {
        $this->freeShippingStatus = ShippingPrice::NOT_FREE;
        return $this;
    }
}