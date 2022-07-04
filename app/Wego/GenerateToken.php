<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 2/15/16
 * Time: 1:29 PM
 */

namespace Wego;
use App\Exceptions\TokenNotGeneratedException;
use App\Http\Controllers\ApiController;
use Dingo\Api\Exception\ValidationHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon;

class GenerateToken
{
    protected $credentials;
    function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function getToken()
    {
        $token = JWTAuth::attempt($this->credentials,['email'=>$this->credentials['email']]);
        if(! $token)
            (new ApiController())->respondWithError('کلمه عبور یا نام کاربری به درستی وارد نشده است');
        return $token;
    }
}