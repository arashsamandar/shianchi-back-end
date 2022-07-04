<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 26/06/16
 * Time: 14:21
 */

namespace Wego\Services\MakeRequest;


class Curl implements MakeRequestInterface{

    protected $curl;
    protected $url;
    protected $payload;
    public function post()
    {
        $this->curl = curl_init();

        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        curl_setopt($this->curl, CURLOPT_POST, sizeof($this->payload));
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->payload);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($this->curl);
        curl_close($this->curl);

        return $result;
    }

    public function get()
    {
        // TODO: Implement get() method.
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setPayload($payload = [])
    {
        $this->payload = $payload;
    }
}
//abstract class CurlRequester
//{
//
//    protected $url;
//    protected $payload;
//    protected $result;
//    protected $curl;
//
//    public abstract function setPayload($data=[]);
//    protected abstract function verify();
//    protected function post()
//    {
//        $this->curl = curl_init();
//
//        curl_setopt($this->curl, CURLOPT_URL, $this->url);
//        curl_setopt($this->curl, CURLOPT_POST, sizeof($this->payload));
//        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->payload);
//        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
//
//        $result = curl_exec($this->curl);
//        curl_close($this->curl);
//
//        return $result;
//    }
//}