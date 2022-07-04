<?php
/**
 * Created by PhpStorm.
 * UserHandle: wb-admin
 * Date: 12/22/15
 * Time: 8:00 PM
 */

namespace App\Http\Controllers;
use Dingo\Api\Exception\ValidationHttpException;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as IlluminateResponse;

use Tymon;

class ApiController extends Controller
{
    protected $statusCode = 200;
    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function respondNotFound($message = 'Not Found!')
    {
        return $this->setStatusCode(404)->respondWithError($message);
    }
    public function respondUnAuthorized($message = 'Authorization Failed')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_UNAUTHORIZED)->respondWithError($message);
    }
    public function respondInternalError($message = 'Internatl error!')
    {
        return $this->setStatusCode(500)->respondWithError($message);
    }
    public function respondAuthenticationError($message = 'Authentication Failed')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_UNAUTHORIZED)->respondWithError($message);
    }
    public function respondErrorOrOk($data=[])
    {
        if ($data['type'] === 'error')
        {
            return $this->respondNotFound($data['message']);
        }
        return $this->respondOk($data['message'],$data['type']);
    }
    public function respondArray($data=[], $headers=[])
    {
        return Response::json($data,$this->getStatusCode(),$headers);
    }

    public function respondErrorArray($data=[], $headers=[])
    {
        $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND);
        return Response::json($data,$this->getStatusCode(),$headers);
    }
    /**
     * @param $message
     * @return mixed
     */
    public function respondWithError($message)
    {
        //$this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND);
        throw new ValidationHttpException([
            [
                [
                    'message' => $message,
                ]
            ]
        ]);
    }
    public function respondOk($message="successful",$type = "message")
    {
        return $this->respond([
            $type =>$message
        ]);
    }
    public function respond($data,$headers=[])
    {

        return Response::json($data,$this->getStatusCode(),$headers);
    }

}