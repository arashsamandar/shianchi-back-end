<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 9/26/17
 * Time: 5:36 PM
 */

namespace Wego\Shipping\Company;


use App\Payment;
use Wego\DeliveryTimeCalculator;
use Wego\Shipping\Price\ShippingPrice;

class Wegobazaar extends AbstractShipping
{
    const WEGOBAZAAR_SHIPPING_PRICE = 5000;
    protected $persianName = 'ارسال عادی';
    protected $company = 'Wegobazaar';
    protected $id = 1;
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
        $this->shippingPrice = self::WEGOBAZAAR_SHIPPING_PRICE;
        return $this;
    }

    protected function time()
    {
        $this->shippingTime = (new DeliveryTimeCalculator())->calculatePossibilities();
        return $this;
    }

    protected function check()
    {
        $this->turn = ($this->address->hasWegoShipping());
        return $this;
    }

    protected function freeShipping()
    {
        $this->freeShippingStatus = ($this->totalProductsPrice >= self::WEGOBAZAAR_FREE_SHIPPING_PRICE) ? ShippingPrice::FREE : ShippingPrice::NOT_FREE;
        return $this;
    }
}