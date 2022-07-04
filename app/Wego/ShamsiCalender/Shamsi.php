<?php

namespace Wego\ShamsiCalender;

include(app_path().'/Wego/ShamsiCalender/jdf.php');

use Carbon\Carbon;

/**
    baraie etelaate bishtar be site http://jdf.scr.ir/ morajee befarmaEd
 */
class Shamsi
{
    public static function convert(Carbon $time){
        $timestamp = '1970-01-01 00:00:00';
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, 'Asia/Tehran');
        $date->setTimezone('UTC');
        return jdate("Y/m/d",$time->diffInSeconds($date));
    }

    public static function timeDetail(Carbon $time){
        $timestamp = '1970-01-01 00:00:00';
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, 'Asia/Tehran');
        $date->setTimezone('UTC');
        return jgetdate($time->diffInSeconds($date));
    }

    public static function convertToGeorgian($shamsiDate){
        $slicedDate = explode('/',$shamsiDate);
        $result = jalali_to_gregorian($slicedDate[0],$slicedDate[1],$slicedDate[2]);
        $carbon = Carbon::create($result[0],$result[1],$result[2]);
        return $carbon->toDateString();
    }
}