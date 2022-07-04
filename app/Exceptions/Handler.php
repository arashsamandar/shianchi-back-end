<?php

namespace App\Exceptions;

use App\Http\Controllers\ApiController;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Tymon;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {

        if ($e instanceof Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json([
                'error' => ['message' => 'token_expired',
                    'status_code' => $e->getStatusCode()]
            ], $e->getStatusCode());
        } else if ($e instanceof Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            return response()->json([
                'error' => ['message' => 'token_invalid',
                    'status_code' => $e->getStatusCode()]
            ], $e->getStatusCode());
        } else if ($e instanceof Tymon\JWTAuth\Exceptions\JWTException) {
            return response()->json([
                'error' => ['message' => 'token_absent',
                    'status_code' => $e->getStatusCode()]
            ], $e->getStatusCode());
        } elseif ($e instanceof NotStaffException) {
            return (new ApiController())->setStatusCode(401)->respondWithError('not a staff member');
        } elseif ($e instanceof NotStoreException) {
            return (new ApiController())->setStatusCode(401)->respondWithError('not a store member');
        } elseif ($e instanceof NotBuyerException) {
            return (new ApiController())->setStatusCode(401)->respondWithError('not a buyer member');
        } elseif ($e instanceof NotPermittedException) {
            return (new ApiController())->setStatusCode(401)->respondWithError('user is not permitted');
        } elseif ($e instanceof BadRequestException) {
            return (new ApiController())->setStatusCode(400)->respondWithError('Bad Request: ' . $e->getMessage());
        } elseif ($e instanceof DoubleSpendingOccurred) {
            return (new ApiController())->setStatusCode(400)->respondWithError('DoubleSpending Happened');
        } elseif ($e instanceof OrderDBException) {
            return (new ApiController())->setStatusCode(404)->respondWithError('order is not created');
        } elseif ($e instanceof MethodNotImplemented) {
            return (new ApiController())->setStatusCode(404)->respondWithError('method is not implemented');
        } elseif ($e instanceof PermissionNotAllowed) {
            return (new ApiController())->setStatusCode(404)->respondWithError(Lang::get('generalMessage.PermissionNotAllowed'));
        } elseif ($e instanceof NullException) {
            return (new ApiController())->respondWithError(Lang::get('generalMessage.notNullException'));
        } elseif ($e instanceof ModelNotFoundException) {
            return (new ApiController())->setStatusCode(404)->respondWithError(Lang::get("generalMessage.NotExistAndCanNotFind"));
        } elseif ($e instanceof FileNotFoundException) {
            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('FileNotFoundException') . $e->getMessage());
        } elseif ($e instanceof TokenNotGeneratedException) {
            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('validation.custom.authentication_failure'));
        } elseif ($e instanceof RecaptchaException) {
            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('validation.custom.g-recaptcha-response.required'));
        } elseif ($e instanceof FailFindColor) {
            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('generalMessage.ColorNotExist'));
        } elseif ($e instanceof ColorIsUsingCanNotUpdateOrDelete) {
            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('generalMessage.ColorIsUsing'));
        } elseif ($e instanceof BazaarHasStaffCanNotUpdate) {
            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('generalMessage.BazaarIsUsing'));
        } elseif ($e instanceof BazaarHasStoreCanNotUpdate) {
            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('generalMessage.BazaarIsUsing'));
        } elseif ($e instanceof ExpiredCouponException) {
            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('generalMessage.CouponIsExpired'));
        } elseif ($e instanceof UsedCouponException){
            return (new ApiController())->setStatusCode(404)->respondInternalError(Lang::get('generalMessage.CouponIsUsed'));
        }


//        else if ($e instanceof QueryException)
//        {
//            return response()->json([
//                'error' => ['message' => 'constraint error',
//                    'status_code' => 404]
//            ], 404);
//        }
        return parent::render($request, $e);
    }
}
