<?php
/**
 * Created by PhpStorm.
 * User: hoseinz3
 * Date: 9/30/2017 AD
 * Time: 15:41
 */

namespace App\Wego\Buy\Payment;


use Carbon\Carbon;

class Online extends AbstractPayment
{

    public function getUrl()
    {
        return
                env('ONLINE_PAYMENT_URL_DOMAIN')
                . 'payWaitingOrder?oi=' . encrypt($this->order->id)
                . '&token=' . $this->token
                . '&ui=' . encrypt($this->userId)
                . '&fp=' . encrypt($this->order->final_order_price);
//                . '&ti=' . encrypt(Carbon::now()->toDateTimeString());
        return "http://shiii.ir/";
//        if ($this->order->customer_type ==1) {
//            return
//                .env('ONLINE_PAYMENT_URL_DOMAIN')
//                . 'waitingOrder?oi=' . encrypt($this->order->id)
//                . '&token=' . $this->token
//                . '&ui=' . encrypt($this->userId)
//                . '&fp=' . encrypt($this->order->final_order_price)
//                . '&ti=' . encrypt(Carbon::now()->toDateTimeString());
//        } else {
//            return
//                .env('ONLINE_PAYMENT_URL_DOMAIN')
//                . 'payWaitingOrder?oi=' . encrypt($this->order->id)
//                . '&token=' . $this->token
//                . '&ui=' . encrypt($this->userId)
//                . '&fp=' . encrypt($this->order->final_order_price);
////                . '&ti=' . encrypt(Carbon::now()->toDateTimeString());
//        }
    }

    private function getAccessTokenFromPayping()
    {
        $fields = 'grant_type=password&username=wegobazaar&password=0000000000';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER,['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_URL, "https://api.payping.ir/token");
        curl_setopt($ch, CURLOPT_POST, 3);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $content = \GuzzleHttp\json_decode($content,true);
        curl_close($ch);
        return $content['access_token'];
    }

    private function getUserKeyFromPayping($accessToken)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER,['Content-Type: application/json','Accept: application/json',
            'Authorization: bearer '.$accessToken]);
        curl_setopt($ch, CURLOPT_URL, "https://api.payping.ir/v1/payment/GetUserKey?username=wegobazaar");
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $content = \GuzzleHttp\json_decode($content,true);
        curl_close($ch);
        return $content;
    }

    private function getPaypingToken($accessToken, $userKey)
    {
        //todo fill return url
        $fields = 'UserKey='.$userKey.'&ReturnUrl=https://api.wegobazaar.com&PayerName='.$this->order->user->name.
            "&Description=پرداخت بابت سفارش شماره ".$this->order->id."&Amount=".$this->order->final_order_price.
            "&ReferenceId=";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER,['Content-Type: application/json','Authorization: bearer '.$accessToken]);
        curl_setopt($ch, CURLOPT_URL, "https://api.payping.ir/v1/payment/RequestToken");
        curl_setopt($ch, CURLOPT_POST, 6);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $content = \GuzzleHttp\json_decode($content,true);
        curl_close($ch);
        return $content['access_token'];
    }

}