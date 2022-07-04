<?php  namespace Wego\UserHandle;
use App\Http\Controllers\ApiController;
use Symfony\Component\HttpFoundation\Request;
use App\Http\Requests;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon;
/**
 * Created by PhpStorm.
 * UserHandle: wb-admin
 * Date: 12/26/15
 * Time: 7:01 PM
 */
class AuthenticationUser extends ApiController
{
    protected $authenticatedUser;
    protected $jwtAuth;



}