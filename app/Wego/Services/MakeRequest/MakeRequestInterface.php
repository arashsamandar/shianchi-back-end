<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 09/02/17
 * Time: 16:33
 */

namespace Wego\Services\MakeRequest;

interface MakeRequestInterface
{
    public function post();
    public function get();
    public function setUrl($url);
    public function setPayload($payload=[]);
}