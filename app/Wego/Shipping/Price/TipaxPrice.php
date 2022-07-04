<?php
/**
 * Created by PhpStorm.
 * User: wb-2
 * Date: 6/8/16
 * Time: 10:16 AM
 */

namespace Wego\Shipping\Price;


use Wego\Province\Util\MemoryProvinceManager;

class TipaxPrice extends ShippingPrice
{
    protected static $nearCities = [
        "آباده" => -1,
        "ابهر" => -1,
        "خرمدره" => -1,
        "آستارا" => -1,
        "آمل" => -1,
        "اراک" => -1,
        "اردبیل" => -1,
        "اردکان" => -1,
        "ارومیه" => -1,
        "اسدآباد" => -1,
        "اشتهارد" => -1,
        "اصفهان" => -1,
        "الوند" => -1,
        "الیگودرز" => -1,
        "اندیمشک" => -1,
        "انزلی" => -1,
        "اهواز" => -1,
        "بابل" => -1,
        "بابلسر" => -1,
        "باغستان" => -1,
        "بروجرد" => -1,
        "بروجن" => -1,
        "بناب" => -1,
        "بهشهر" => -1,
        "تاکستان" => -1,
        "تالش" => -1,
        "تبریز" => -1,
        "نتکابن" => -1,
        "چالوس" => -1,
        "چابهار" => -1,
        "خرم آباد" => -1,
        "خمینی شهر" => -1,
        "دامغان" => -1,
        "دورود" => -1,
        "دلیجان" => -1,
        "دزفول" => -1,
        "رامسر" => -1,
        "رشت" => -1,
        "رفسنجان" => -1,
        "رودسر" => -1,
        "زرین شهر" => -1,
        "زرند" => -1,
        "زنجان" => -1,
        "ساری" => -1,
        "ساوه" => -1,
        "سبزوار" => -1,
        "سمنان" => -1,
        "سنندج" => -1,
        "شاهرود" => -1,
        "شاهین شهر" => -1,
        "شوش" => -1,
        "شوشتر" => -1,
        "شهرضا" => -1,
        "شهرکرد" => -1,
        "شهر صنعتی البرز" => -1,
        "شیراز" => -1,
        "صائین قلعه" => -1,
        "صومعه سرا" => -1,
        "فومن" => -1,
        "عسلویه" => -1,
        "علی آباد" => -1,
        "فردیس" => -1,
        "قائم شهر" => -1,
        "قروه" => -1,
        "قزوین" => -1,
        "قشم" => -1,
        "قم" => -1,
        "کاشان" => -1,
        "کرج" => -1,
        "کرد کوی" => -1,
        "کرمان" => -1,
        "کرمانشاه" => -1,
        "کمال شهر" => -1,
        "کیش" => -1,
        "گرگان" => -1,
        "گنبد" => -1,
        "لاهیجان" => -1,
        "لنگرود" => -1,
        "محمد شهر" => -1,
        "محمدیه" => -1,
        "مراغه" => -1,
        "مرودشت" => -1,
        "مشکین دشت" => -1,
        "مشهد" => -1,
        "ماهشهر" => -1,
        "ملایر" => -1,
        "مهاباد" => -1,
        "میاندوآب" => -1,
        "میانه" => -1,
        "میبد" => -1,
        "نجف آباد" => -1,
        "نکا" => -1,
        "نور" => -1,
        "نوشهر" => -1,
        "نهاوند" => -1,
        "نیشابور" => -1,
        "ورامین" => -1,
        "همدان" => -1,
        "هیدج" => -1,
        "یزد" => -1,
    ];
    protected static $farCities = [
        "آبادان" => -1,
        "اسلام آباد غرب" => -1,
        "ایلام" => -1,
        "بجنورد" => -1,
        "برازجان" => -1,
        "بم" => -1,
        "بندر عباس" => -1,
        "بوشهر" => -1,
        "بهبهان" => -1,
        "بیرجند" => -1,
        "بوکان" => -1,
        "پیرانشهر" => -1,
        "تربت حیدریه" => -1,
        "جهرم" => -1,
        "جیرفت" => -1,
        "خرمشهر" => -1,
        "خوی" => -1,
        "داراب" => -1,
        "دهدشت" => -1,
        "زاهدان" => -1,
        "سقز" => -1,
        "سیرجان" => -1,
        "شیروان" => -1,
        "فریمان" => -1,
        "فسا" => -1,
        "قوچان" => -1,
        "گچساران" => -1,
        "کازرون" => -1,
        "مرند" => -1,
        "لار" => -1,
        "لامرد" => -1,
        "ماکو" => -1,
        "یاسوج" => -1,
        "بندر ماهشهر" => -1,
        "نورآباد ممسنی" => -1
    ];
    protected $provinceManager;



    function __construct(){
        $this->provinceManager = new MemoryProvinceManager();
    }
    public function get()
    {
        $this->checkIfVariablesIsSet();
        $this->checkSourceVariables();
        $this->checkDestinationVariables();
        return $this->calculatePrice();
    }

    /**
     * TODO : kamel she
     */
    public function checkIfVariablesIsSet()
    {
        parent::checkIfVariablesIsSet();
    }

    private function checkSourceVariables()
    {
        if($this->getSourceProvinceId() === ShippingPrice::TEHRAN_ID )
            if($this->getSourceCityId() === ShippingPrice::TEHRAN_CITY_ID)
                return true;
        return false;
    }
    /**
     * TODO : kamel she
     */
    private function checkDestinationVariables()
    {
        return true;
    }

    private function calculatePrice()
    {
        $result = -1;
        if($this->isDestinationNear()) {
            $result = $this->calculateNearPrice();
        }elseif($this->isDestinationFar()){
            $result = $this->calculateFarPrice();
        }
        return $result;
    }

    private function isDestinationNear()
    {
        $keys = array_keys($this::$nearCities);
        $province = $this->provinceManager->getProvince($this->getDestinationProvinceId());
        $city = $this->provinceManager->getCity($province,$this->getDestinationCityId());
        if(in_array($city["Title"] , $keys))
            return true;
        return false;
    }

    private function calculateNearPrice()
    {
        $service = 27000;
        if ($this->weight < 1000){
            $transport= 141000;
            $service = 0;
        } elseif ($this->weight <=3000){
            $transport = 140000;
            $service = 0;
        } elseif($this->weight <= 6000){
            $transport = 140000;
        }elseif($this->weight <= 30000){
            $transport = 140000 + ceil(($this->weight - 6000)/1000) * 17000;
        }else{
            $transport = 560000 + ceil(($this->weight - 31000)/1000) * 15000;
        }
        return $this->makeJsonResult($transport,$service);
    }

    private function isDestinationFar()
    {
        $keys = array_keys($this::$farCities);
        $province = $this->provinceManager->getProvince($this->getDestinationProvinceId());
        $city = $this->provinceManager->getCity($province,$this->getDestinationCityId());
        if(in_array($city["Title"] , $keys))
            return true;
        return false;
    }

    private function calculateFarPrice()
    {
        $service = 27000;
        if ($this->weight < 1000){
            $transport= 120000;
            $service = 0;
        } elseif ($this->weight <=3000){
            $transport = 150000;
            $service = 0;
        } elseif($this->weight <= 6000){
            $transport = 157000;
        } elseif($this->weight <= 30000){
            $transport = 157000 + ceil(($this->weight - 6000)/1000) * 17000;
        }else{
            $transport = 560000 + ceil(($this->weight - 31000)/1000) * 15000;
        }
        return $this->makeJsonResult($transport,$service);
    }

    private function makeJsonResult($transport, $service)
    {
        $cache = (.09)*($transport + $service);
        return (ceil(($transport + $service + $cache)/10));
    }
}