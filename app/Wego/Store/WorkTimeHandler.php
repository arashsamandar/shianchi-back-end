<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 09/05/16
 * Time: 12:12
 */

namespace Wego\Store;


use App\Store;
use App\WorkTime;
use Carbon\Carbon;

class WorkTimeHandler implements StorePivotHandlerInterface
{
    const HOLIDAY = -1;

    public function save(array $requests, Store $store)
    {
        $workTimeArray = [];
        if(count($requests) > 8)
            return;
        foreach ($requests as $request) {
            $workTimeArray[] = new WorkTime($this->changeWorkTimeStyle($request));
        }
        $store->work_times()->saveMany($workTimeArray);
    }

    public static function changeWorkTimeStyle($request)
    {

        $isHoliday = self::isHoliday($request['opening_time'],$request['closing_time']);
        $request['is_closed'] = $isHoliday;
        $request['opening_time'] = $isHoliday ? (self::HOLIDAY) : $request['opening_time'];
        $request['closing_time'] = $isHoliday ? (self::HOLIDAY) : $request['closing_time'];

        return $request;
    }

    public static function isHoliday($openingTime,$closingTime)
    {
        return ($openingTime == self::HOLIDAY ||
                $closingTime == self::HOLIDAY) ? 1: 0;
    }
}