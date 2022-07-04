<?php
/**
 * Created by PhpStorm.
 * User: hoseinz3
 * Date: 9/30/2017 AD
 * Time: 15:43
 */

namespace App\Wego\Buy\Payment;


use App\Payment;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class PaymentFactory
{

    public static function getPayment($paymentId)
    {
        switch ($paymentId){
            case Payment::ONLINE:
                return (new Online());
            case Payment::CARD:
                return (new Offline());
            case Payment::CASH:
                return (new Offline());
            default:
                throw new InvalidArgumentException();
        }
    }
}