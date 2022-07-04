<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 10/23/16
 * Time: 4:08 PM
 */

namespace Wego;


use App\Holiday;
use App\Order;
use Carbon\Carbon;
use Wego\ShamsiCalender\Shamsi;

class DeliveryTimeCalculator
{
    const FIRST_DELIVERY_TIME = "10-22";
    const SECOND_DELIVERY_TIME = "15-21";
    const DATE_TIME_DELIMITER = '&';
    protected $now;
    protected $possibleDeliveryTimes = [];
    protected $deliveryTimesInDay;
    protected $daysOfDelivery;
    protected $notPossibleDeliveryTime;
    protected $isFirstDay;
    protected $firstDayDeliveryTimeIndex;
    protected $dayTranslator = [
        "sunday" => 0,
        "monday" => 1,
        "tuesday" => 2,
        "wednesday" => 3,
        "thursday" => 4,
        "friday" => 5,
        "saturday" => 6
    ];
    protected $notDeliveryDay = [5];

    /**
     *
     */
    function __construct()
    {
        $this->deliveryTimesInDay = [self::FIRST_DELIVERY_TIME];
        $this->daysOfDelivery = 3;
        $this->notPossibleDeliveryTime = [];
        $this->now = Carbon::now();
    }

    /**
     * @param $days
     * @return $this
     */
    public function setNotDeliveryDays($days)
    {
        foreach ($days as $day) {
            $this->notDeliveryDay[] = $this->dayTranslator[$day];
        }
        return $this;
    }

    /**
     * @param $deliveryDays
     * @return $this
     */
    public function setDaysOfDelivery($deliveryDays)
    {
        $this->daysOfDelivery = $deliveryDays;
        return $this;
    }

    /**
     * @param $deliveryTimes
     * @return $this
     */
    public function setDeliveryTimes($deliveryTimes)
    {
        $this->deliveryTimesInDay = $deliveryTimes;
        return $this;
    }

    /**
     * @param $days
     * @param $time
     * @return $this
     */
    public function setNotPossibleDeliveryTimes($days, $time)
    { //setNotPossibleDeliveryTimes(['sunday','monday'],[[14-20,'14-15'],[['14-20']])
        foreach ($days as $key => $day) {
            $days[$key] = $this->dayTranslator[$day];
        }
        $this->notPossibleDeliveryTime = array_combine($days, $time);
        return $this;
    }

    public function calculatePossibilities()
    {
        $date = $this->getWegoJetDate();
        $possibleDate = $date->startOfDay();
//        $possibleDate = $possibleDate->addDay();
        $possibleDeliveryTimes = [];
        for ($deliveryDayNumber = 0; $deliveryDayNumber < $this->daysOfDelivery; $deliveryDayNumber++) {
            $possibleDate = $possibleDate->addDay(1);
            $possibleDate = $this->skipImpossibleDays($possibleDate);
            $possibleDeliveryTimes[] = $this->fillPossibleDeliveryTimes($possibleDate, $deliveryDayNumber);
        }
        return $possibleDeliveryTimes;
    }
    public function calculatePossibilitiesForStep()
    {
        $date = $this->getWegoJetDate();
        $possibleDate = $date->startOfDay();
        $possibleDeliveryTimes = [];
        $possibleDate = $possibleDate->addDay(1);
        $possibleDate = $this->skipWegojetImpossibleDays($possibleDate);
        $counter = 1;
        $orderCounts= [];
        while($this->isWegobazaarHoliday($possibleDate) && $counter < 4){
            $wegobazaarOrders = Order::where('shipping_company', 'wegobazaar')
                ->where('delivery_time', 'like', '%' . $possibleDate->toDateString() . '%')
                ->where('status','<>',Order::CANCELLED)->count();
            if ($possibleDate->dayOfWeek == 4){
                if ($wegobazaarOrders >= 3 && !($this->isWegobazaarHoliday($date))){
                    $holiday = ['holiday'=>$date->toDateString(),'created_at'=>$date->subDay(1)];
                    Holiday::create($holiday);
                }
                $possibleDate = $possibleDate->addDay();
            } else {
                $orderCounts[] = ['date'=>$possibleDate->toDateString(),'count'=>$wegobazaarOrders];
                $counter++;
                $possibleDate = $possibleDate->addDay();
            }
            $possibleDate = $this->skipWegojetImpossibleDays($possibleDate);
        }
        if ($counter < 4){
            $wegobazaarOrders = Order::where('shipping_company', 'wegobazaar')
                ->where('delivery_time', 'like', '%' . $possibleDate->toDateString() . '%')
                ->where('status','<>',Order::CANCELLED)->count();
            $count = $wegobazaarOrders ;
            if ($possibleDate->dayOfWeek == 4){
                $orderLimit = 3;
            } else {
                $k = 10;
                while($count > $k) {
                    $k = $k + 2;
                }
                $orderLimit = $k ;
            }
            if ($count == $orderLimit && !($this->isWegobazaarHoliday($date))){
                $holiday = ['holiday'=>$date->toDateString(),'created_at'=>$date->subDay(1)];
                Holiday::create($holiday);
                $this->calculatePossibilitiesForStep();
            }
        } else {
            $index = array_search(min(array_column($orderCounts, 'count')), array_column($orderCounts, 'count'));
            $orderCount = $orderCounts[$index];
            Holiday::where('holiday',$orderCount['date'])->delete();
        }
        return $possibleDeliveryTimes;
    }


    public function dayHasImpossibleTime($deliveryTime)
    {
        return (array_key_exists($deliveryTime->dayOfWeek, $this->notPossibleDeliveryTime));
    }

    public function isNotImpossibleTime($deliverTime, $deliveryTime)
    {
        if (!array_key_exists($deliveryTime->dayOfWeek, $this->notPossibleDeliveryTime))
            return true;
        else
            return (array_search($deliverTime, $this->notPossibleDeliveryTime[$deliveryTime->dayOfWeek]) === false);
    }

    /**
     * @param $deliveryTime
     * @return bool
     */
    public function isImpossibleDay($deliveryTime)
    {
        return (array_search($deliveryTime->dayOfWeek, $this->notDeliveryDay) !== false);
    }

    public function skipImpossibleDays($possibleDate)
    {
        while ($this->isImpossibleDay($possibleDate) || $this->isHoliday($possibleDate)) {
             $possibleDate = $possibleDate->addDay(1);
        }
        return $possibleDate;
    }

    public function skipWegojetImpossibleDays($possibleDate)
    {
        while ($this->isImpossibleDay($possibleDate) || $this->isWegojetHoliday($possibleDate)) {
            $possibleDate = $possibleDate->addDay(1);
        }
        return $possibleDate;
    }

    public function fillPossibleDeliveryTimes($possibleDate, $deliveryDayNumber)
    {
        foreach ($this->deliveryTimesInDay as $possibleTime) {
//            if($possibleTime == self::FIRST_DELIVERY_TIME && $this->firstDayDeliveryTimeIndex==1 && $this->isFirstDay){
//                continue;
//            }
            if ($this->isNotImpossibleTime($possibleTime, $possibleDate)) {
                $dateTimes[$deliveryDayNumber][] = $this->formatDateTime($possibleDate,$possibleTime);
                $possibleDeliveryTimes[$deliveryDayNumber] = ['day' => Shamsi::timeDetail($possibleDate)['weekday'], 'time' => $dateTimes[$deliveryDayNumber]];
            }
        }
        return $possibleDeliveryTimes[$deliveryDayNumber];
    }

    private function skipOneDayIfPurchaseStartTimeHasPassed($date)
    {
        $this->firstDayDeliveryTimeIndex = 0;
        $this->isFirstDay = true;
        if (strtotime($date->toTimeString()) >= strtotime(Order::PURCHASE_START_TIME)) {
            $date = $date->addDay(1);
        } else {
            $this->firstDayDeliveryTimeIndex = 1;
        }
        while ($this->isImpossibleDay($date) || $this->isHoliday($date)) {
            $date = $date->addDay(1);
        }
        return $date;
    }

    private function formatDateTime($possibleDate, $possibleTime)
    {
        $this->isFirstDay = false;
        return $possibleDate->toDateString() . self::DATE_TIME_DELIMITER . $possibleTime;
    }

    private function skipHolidays($possibleDate)
    {
        if (!is_null(Holiday::where('holiday',$possibleDate->toDateString())->first())){
            return $possibleDate->addDay(1);
        }
        return $possibleDate;
    }

    /**
     * @param $possibleDate
     * @return bool
     */
    public function isHoliday($possibleDate)
    {
        return !is_null(Holiday::where('holiday', $possibleDate->toDateString())->first());
    }

    public function isWegojetHoliday($possibleDate)
    {
        $checkingDate = $possibleDate->copy();
        return !is_null(Holiday::where('holiday', $possibleDate->toDateString())
            ->where('created_at','<=',$checkingDate->subDays(5))->first());
    }
    public function isWegobazaarHoliday($possibleDate)
    {
        $checkingDate = $possibleDate->copy();
        return !is_null(Holiday::where('holiday', $possibleDate->toDateString())
            ->where('created_at','>',$checkingDate->subDays(5))->first());
    }

    public static function differenceFromNow($time)
    {
        $expirationTime = Carbon::parse($time);
        $result = Carbon::now()->diffInSeconds($expirationTime, false);
        return $result;
    }
    public function calculateWegojetDeliveryTime()
    {
        $date = Carbon::now();
        if ($date->dayOfWeek == 4){
            $timeLimit = "12:00:00";
        } else {
            $timeLimit = Order::PURCHASE_START_TIME ;
        }
        if(strtotime($date->toTimeString()) <= strtotime($timeLimit) && strtotime($date->toTimeString()) >= strtotime("10:00:00")){
            if($this->isImpossibleDay($date) || $this->isWegojetHoliday($date)){
                $date = $date->addDay();
                $date = $this->skipWegojetImpossibleDays($date);
                $time = 'حداکثر تا ساعت 18 روز '.Shamsi::timeDetail($date)['weekday'].' مورخ '.Shamsi::convert($date);
            }else{
                $time = "حداکثر تا هفت ساعت بعد از ثبت سفارش";
            }
        }
        elseif(strtotime($date->toTimeString()) >= strtotime($timeLimit)){
            $date = $date->addDay()->subHours(3)->subMinutes(30);
            $date = $this->skipWegojetImpossibleDays($date);
            $time = 'حداکثر تا ساعت 18 روز '.Shamsi::timeDetail($date)['weekday'].' مورخ '.Shamsi::convert($date);
        }
        elseif(strtotime($date->toTimeString()) <= strtotime("10:00:00")){
            $date = $this->skipWegojetImpossibleDays($date);
            $time = 'حداکثر تا ساعت 18 روز '.Shamsi::timeDetail($date)['weekday'].' مورخ '.Shamsi::convert($date);
        }
        return $time;
    }

    public function getWegoJetDate()
    {
        $date = Carbon::now();
        if ($date->dayOfWeek == 4){
            $timeLimit = "12:00:00";
        } else {
            $timeLimit = Order::PURCHASE_START_TIME ;
        }
        if(strtotime($date->toTimeString()) <= strtotime($timeLimit) && strtotime($date->toTimeString()) >= strtotime("10:00:00")){
            if($this->isWegojetHoliday($date)){
                $date = $date->addDay();
                $date = $this->skipWegojetImpossibleDays($date);
            }
        }
        elseif(strtotime($date->toTimeString()) >= strtotime($timeLimit)){
            $date = $date->addDay()->subHours(3)->subMinutes(30);
            $date = $this->skipWegojetImpossibleDays($date);
        }
        elseif(strtotime($date->toTimeString()) <= strtotime("10:00:00")){
            $date = $this->skipWegojetImpossibleDays($date);
        }
        return $date;
    }
}