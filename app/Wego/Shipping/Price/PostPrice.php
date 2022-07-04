<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 22/05/16
 * Time: 15:47
 */

namespace Wego\Shipping\Price;


class PostPrice extends ShippingPrice
{
    protected $url,$insurance,$postalRent,$cache,$nonStandard,$totalPrice,$postalShare;

    const INSURANCE = 6500;

    /**
     * @return float
     */
    public function get()
    {

        if($this->getDestinationProvinceId() == ShippingPrice::TEHRAN_ID && $this->getSourceProvinceId() == ShippingPrice::TEHRAN_ID)
        {
            $this->tehranPrice();

            return $this->mapResult();
        }
        $this->otherCityPrice();
        return $this->mapResult();
    }

    /**
     * @return mixed
     */
    private function tehranPrice()
    {
        if ((int)$this->getWeight() < 251)
            return $this->setPostalRent(48000);
        else if ((int)$this->getWeight() < 501)
            return $this->setPostalRent(57000);
        else if ((int)$this->getWeight() < 1001)
            return $this->setPostalRent(69000);
        else
            return $this->setPostalRent(91000 +( floor(((int)$this->getWeight() - 2000)/1000) *20000) );
    }

    /**
     * @return mixed
     */
    private function otherCityPrice()
    {
        if ((int)$this->getWeight() < 501)
            return $this->setPostalRent(60000);
        else if ((int)$this->getWeight() < 1001)
            return $this->setPostalRent(80000);
        else
            return $this->setPostalRent(126000 +( floor(((int)$this->getWeight() - 2000)/1000) *29000) );
    }

    /**
     * @param $postalRent
     * @return mixed
     */
    private function setPostalRent($postalRent)
    {
        $this->postalRent = $postalRent;

        return $this->calculateNonStandard();
    }

    /**
     * @return mixed
     */
    private function calculateNonStandard()
    {
        $this->nonStandard = $this->postalRent/10;

        return $this->calculatePostalShare();
    }

    /**
     * @return mixed
     */
    private function calculatePostalShare()
    {
        $this->postalShare = $this->nonStandard + $this->postalRent + self::INSURANCE;
        return $this->calculateCache();
    }

    /**
     * @return mixed
     */
    private function calculateCache()
    {
        $this->cache = (.09)*$this->postalShare;
        return $this->calculateTotalPrice();
    }

    /**
     * @return mixed
     */
    private function calculateTotalPrice()
    {
        return $this->totalPrice = $this->postalShare + $this->cache;
    }

    /**
     * @return float
     */
    private function mapResult()
    {
//        return [
//            "کرایه پستی :"=> $this->postalRent,
//            "مالیات بر ارزش افزوده :" => $this->cache,
//            "غیر استاندارد :" => $this->nonStandard,
//            "نرخ بیمه :" => self::INSURANCE,
//            "حق السهم پست:" => $this->postalShare,
//            "جمع کل :" => $this->totalPrice
//        ];
        if($this->weight > 25000){
            return 0;
        }
//        3400 poorsant for taarof
        return ((ceil($this->totalPrice/10))+1000+3400);
    }
}