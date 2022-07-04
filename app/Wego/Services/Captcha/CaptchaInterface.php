<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 09/02/17
 * Time: 16:38
 */

namespace Wego\Services\Captcha;


interface CaptchaInterface
{
    public function verify();

    public function setPayload($payload=[]);
}