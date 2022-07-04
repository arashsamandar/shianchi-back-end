<?php

namespace App\Providers;

use App\Exceptions\BadRequestException;
use App\Exceptions\BazaarHasStaffCanNotUpdate;
use App\Exceptions\BazaarHasStoreCanNotUpdate;
use App\Exceptions\ColorIsUsingCanNotUpdateOrDelete;
use App\Exceptions\DoubleSpendingOccurred;
use App\Exceptions\ExpiredCouponException;
use App\Exceptions\FailFindColor;
use App\Exceptions\FileNotFoundException;
use App\Exceptions\Handler;
use App\Exceptions\MethodNotImplemented;
use App\Exceptions\NotBuyerException;
use App\Exceptions\NotPermittedException;
use App\Exceptions\NotStaffException;
use App\Exceptions\NotStoreException;
use App\Exceptions\NullException;
use App\Exceptions\OrderDBException;
use App\Exceptions\PermissionNotAllowed;
use App\Exceptions\RecaptchaException;
use App\Exceptions\TokenNotGeneratedException;
use App\Exceptions\UsedCouponException;
use App\Http\Controllers\ApiController;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Symfony\Component\Debug\ExceptionHandler;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

/**
 * Class ExceptionsServiceProvider - Hacky?!
 * @package App\Providers
 */
class ExceptionsServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(){}

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
//        $this->app->singleton('api.exception', function ($app) {
//        return new ExceptionHandler($app['Illuminate\Contracts\Debug\ExceptionHandler'], $this->config('errorFormat'), $this->config('debug'));
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (Exception $exception) {
//            return app('App\Exceptions\Handler')->render(req,$exception);
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (TokenExpiredException $e) {
//            return response()->json([
//                'error' => ['message' => 'token_expired',
//                    'status_code' => $e->getStatusCode()] ], $e->getStatusCode());
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (TokenInvalidException $e) {
//            return response()->json([
//                'error' => ['message' => 'token_invalid',
//                    'status_code' => $e->getStatusCode()]
//            ], $e->getStatusCode());
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (JWTException $e) {
//            return response()->json([
//                'error' => ['message' => 'token_absent',
//                    'status_code' => $e->getStatusCode()]
//            ], $e->getStatusCode());
//        });
        app('Dingo\Api\Exception\Handler')->register(function (ModelNotFoundException $e) {
            return (new ApiController())->setStatusCode(401)->respondWithError('موردی پیدا نشد');
        });
//        app('Dingo\Api\Exception\Handler')->register(function (NotStoreException $e) {
//            return (new ApiController())->setStatusCode(401)->respondWithError('not a store member');
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (NotBuyerException $e) {
//            return (new ApiController())->setStatusCode(401)->respondWithError('not a buyer member');
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (NotPermittedException $e) {
//            return (new ApiController())->setStatusCode(401)->respondWithError('user is not permitted');
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (BadRequestException $e) {
//            return (new ApiController())->setStatusCode(400)->respondWithError('Bad Request: ' . $e->getMessage());
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (DoubleSpendingOccurred $e) {
//            return (new ApiController())->setStatusCode(400)->respondWithError('DoubleSpending Happened');
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (OrderDBException $e) {
//            return (new ApiController())->setStatusCode(404)->respondWithError('order is not created');
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (MethodNotImplemented $e) {
//            return (new ApiController())->setStatusCode(404)->respondWithError('method is not implemented');
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (PermissionNotAllowed $e) {
//            return (new ApiController())->setStatusCode(404)->respondWithError(Lang::get('generalMessage.PermissionNotAllowed'));
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (NullException $e) {
//            return (new ApiController())->respondWithError(Lang::get('generalMessage.notNullException'));
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (ModelNotFoundException $e) {
//            return (new ApiController())->setStatusCode(404)->respondWithError(Lang::get("generalMessage.NotExistAndCanNotFind"));
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (FileNotFoundException $e) {
//            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('FileNotFoundException') . $e->getMessage());
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (TokenNotGeneratedException $e) {
//            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('validation.custom.authentication_failure'));
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (RecaptchaException $e) {
//            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('validation.custom.g-recaptcha-response.required'));
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (FailFindColor $e) {
//            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('generalMessage.ColorNotExist'));
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (ColorIsUsingCanNotUpdateOrDelete $e) {
//            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('generalMessage.ColorIsUsing'));
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (BazaarHasStaffCanNotUpdate $e) {
//            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('generalMessage.BazaarIsUsing'));
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (ExpiredCouponException $e) {
//            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('generalMessage.CouponIsExpired'));
//        });
//        app('Dingo\Api\Exception\Handler')->register(function (UsedCouponException $e) {
//            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('generalMessage.CouponIsUsed'));
//        });


    }
}