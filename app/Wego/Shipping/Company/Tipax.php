<?php
namespace Wego\Shipping\Company;

use App\Payment;
use Wego\Shipping\Price\ShippingPrice;
use Wego\Shipping\Price\TipaxPrice;

class Tipax extends AbstractShipping
{
    protected $persianName = 'تیپاکس';
    protected $company = 'Tipax';
    protected $id = 3;
    protected $payment = [
        [
            'key' => 'پرداخت کارتی در محل', 'status' => true, 'id' => Payment::CARD
        ],
        [
            'key' => 'پرداخت نقدی در محل', 'status' => true, 'id' => Payment::CASH
        ]
    ];


    protected function price()
    {
        $this->shippingPrice = (new TipaxPrice())
            ->setWeight($this->totalWeight+700)
            ->setAddress($this->address)
            ->get();
        return $this;
    }

    protected function time()
    {
        $this->shippingTime = 'سه تا پنج روز کاری';
        return $this;
    }

    protected function check()
    {
        $this->turn = ($this->shippingPrice != -1);
        return $this;
    }

    protected function freeShipping()
    {
        $this->freeShippingStatus = ShippingPrice::NOT_FREE;
        return $this;
    }
}