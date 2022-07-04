<?php
/**
 * Created by PhpStorm.
 * User: hoseinz3
 * Date: 9/30/2017 AD
 * Time: 15:40
 */

namespace App\Wego\Buy\Payment;


abstract class AbstractPayment
{
    protected $order;
    protected $token;
    protected $userId;

    abstract public function getUrl();

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

}