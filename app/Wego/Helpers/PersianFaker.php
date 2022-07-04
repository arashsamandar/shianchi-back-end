<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 03/11/16
 * Time: 14:26
 */

namespace Wego\Helpers;


class PersianFaker
{
    public static function getName()
    {
        $name = [
            'حسین','محمد حسین','محمد کاظم','احمد',
            'قادر','سید ابوالقاسم','سید محمد حسین'
        ];

        return self::getRandomItem($name);
    }
    public static function getFamily(){
        $family= [
            'عابدی','ایللو','توحید لو','عابدی هزاوه','ساسان نژاد اصل',
            'حاجی قربانی فیروزی','منافی راد اصل','پوریاوری','غندالی','روحانی',
        ];
        return self::getRandomItem($family);
    }
    public static function getMobilePrefix()
    {
        $mobilePrefix = [
            '0911','0912','0913','0914','0915','0916','0917','0918',
            '0931','0932','0934','0919','0910',"0901", "0902", "0930",
            "0933", "0935" , "0936" , "0937", "0938" , "0939"
        ];
        return self::getRandomItem($mobilePrefix);
    }
    public static function getProvincePrefix(){
        $provincePrefix = ['021','0861','0511'];
        return self::getRandomItem($provincePrefix);
    }
    public static function getSentence()
    {
        $sentence = [
            'این جا خوب و زیبا است',
            'برای این که بتوانیم بهترین باشیم تلاش می کنیم',
            'چرا عاقل کند کاری که باز آرد پشیمانی',
            'در طلب وصال او همچو شراب گشته ام',
            'میازار موری که دانه کش است',
            'دیشب بهار دسته جمعه رفته بودیم زیارت',
            'بهترین کیفیت نشان دهنده ما است',
            'از بهترین فروشگاه خرید نمایید'
        ];
        return self::getRandomItem($sentence);
    }

    private static function getRandomItem($array=[])
    {
        return $array[rand(0,count($array)-1)];
    }

}