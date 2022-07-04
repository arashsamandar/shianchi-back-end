<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 9/26/17
 * Time: 5:36 PM
 */

namespace Wego\Shipping\Company;

use App\Payment;
use Wego\Shipping\Price\PostPrice;
use Wego\Shipping\Price\ShippingPrice;

class Post extends AbstractShipping
{

    protected $persianName = 'پست';
    protected $company = 'Post';
    protected $id = 2;
    protected $payment = [
        [
            'key' => 'پرداخت آنلاین', 'status' => true, 'id' => Payment::ONLINE
        ]
    ];

    protected function price()
    {
        $this->shippingPrice = (new PostPrice())
            ->setWeight($this->totalWeight+700)
            ->setAddress($this->address)
            ->get();
        return $this;
    }

    protected function time()
    {
        $this->shippingTime = 'سه تا هفت روز کاری';
        return $this;
    }


    protected function check()
    {
//        if($this->totalWeight < 30000){
//            $this->payment[] = ['key' => 'پرداخت نقدی در محل', 'status' => true, 'id' => Payment::CASH];
            $this->payment[] = ['key' => 'پرداخت کارتی در محل', 'status' => true, 'id' => Payment::CARD];
//        }
        $this->turn = (!($this->address->hasWegoShipping())) && (!empty($this->payment));
        return $this;
    }

    protected function freeShipping()
    {
        $this->freeShippingStatus = ShippingPrice::NOT_FREE;
//        $this->freeShippingStatus = ShippingPrice::NOT_FREE;
        return $this;
    }
}