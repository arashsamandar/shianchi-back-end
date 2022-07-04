<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 20/06/16
 * Time: 11:00
 */

namespace Wego\Helpers;


class PersianUtil
{
    public static function to_persian_num($string) {
        //arrays of persian and latin numbers
        $persian_num = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $latin_num = range(0, 9);

        $string = str_replace($latin_num, $persian_num, $string);

        return $string;
    }
    public static function to_english_num($string) {
        //arrays of persian and latin numbers
        $persian_num = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $latin_num = range(0, 9);

        $string = str_replace($persian_num,$latin_num, $string);

        return $string;
    }

    public static function toStandardPersianString($string){
        $stdString = str_replace('ي','ی',$string);
        return $stdString;
    }
}