<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 09/02/17
 * Time: 16:38
 */

namespace Wego\Services\Captcha;


use App\Exceptions\RecaptchaException;
use App\Http\Controllers\ApiController;
use Wego\Services\MakeRequest\MakeRequestInterface;

class GoogleRecaptcha implements CaptchaInterface
{
    protected $request;
    function __construct(MakeRequestInterface $request){
        $this->request = $request;
    }


    public function verify()
    {
        $this->request->setUrl(env('RECAPTCHA_URL'));

        $result = json_decode($this->request->post());
        if(! $result->success)
            (new ApiController())->respondWithError('لطفا از کادر سفید روی قسمت من ربات نیستم کلیک نمایید');
        return $result;
    }

    public function setPayload($payload=[])
    {
        $payload['secret'] = env('RECAPTCHA_SECRET_KEY');
        $this->request->setPayload($payload);
        return $this;
    }
}