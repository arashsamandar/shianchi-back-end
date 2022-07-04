<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    const ONLINE = 1;
    const CASH = 2;
    const CARD = 3;

    public static function isProgressable($paymentId)
    {
        switch ($paymentId) {
            case self::CARD:
                return true;
            case self::CASH:
                return true;
            case self::ONLINE:
                return false;
            default:
                throw new Exception('undefined payment id');
        }
    }
}
