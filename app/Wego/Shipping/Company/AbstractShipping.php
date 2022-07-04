<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 9/26/17
 * Time: 2:38 PM
 */

namespace Wego\Shipping\Company;


use App\BuyerAddress;
use App\Payment;
use Wego\Shipping\Price\ShippingPrice;

abstract class AbstractShipping
{
    const WEGO_DELIVERY_COMPANY = 'wegobazaar';
    const WEGO_JET = 'wego_jet';

    protected $payment;
    protected $persianName;
    protected $company;
    protected $id;
    protected $shippingTime;
    protected $turn;
    protected $totalWeight;
    protected $totalProductsPrice;
    protected $shippingPrice;
    protected $address;
    protected $freeShippingStatus;

    const WEGOBAZAAR_FREE_SHIPPING_PRICE = 100000;

    abstract protected function freeShipping();


    abstract protected function time();

    abstract protected function price();

    abstract protected function check();

    protected function toArray()
    {
        return [
            'payment' => $this->payment,
            'persian_name' => $this->persianName,
            'company' => $this->company,
            'shipping_id' => $this->id,
            'shipping_time' => $this->shippingTime,
            'price' => $this->shippingPrice,
            'status' => $this->freeShippingStatus,
            'real_price' => $this->getRealPrice()
        ];
    }


    public function get()
    {
        $this->time()->freeShipping()->price()->check();
        if ($this->turn) return $this->toArray();
        return [];
    }

    public function setAddress(BuyerAddress $address)
    {
        $this->address = $address;
        return $this;
    }

    public function setTotalWeight($totalWeight)
    {
        $this->totalWeight = $totalWeight;
        return $this;
    }

    public function setTotalProductsPrice($totalProductsPrice)
    {
        $this->totalProductsPrice = $totalProductsPrice;
        return $this;
    }

    protected function getRealPrice()
    {
        return (strcmp($this->freeShippingStatus, ShippingPrice::FREE) ? $this->shippingPrice : 0);
    }

}