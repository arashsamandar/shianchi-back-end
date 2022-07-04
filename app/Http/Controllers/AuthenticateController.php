<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;

use App\Repositories\UserRepository;
use App\User;
use App\Http\Requests;
use App\UserValidToken;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
use Dingo\Api\Routing\Helpers;
use Tymon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Wego\GenerateToken;
use Wego\AuthenticateResponseFormat;

class AuthenticateController extends ApiController
{
    use Helpers;
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->middleware('jwt.auth', ['except' => ['store','index','checkTokenValidity']]);
        $this->userRepository = $userRepository;
    }

    public function store(LoginUserRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $token = (new GenerateToken($credentials))->getToken();

        $user = $this->userRepository->firstByField('email',$request->input('email'));

        UserValidToken::create(['user_email' => $request->input('email') , 'token' => $token]);

        return $this->respondArray((new AuthenticateResponseFormat($user,$token))->format());

    }

    private static function getValidTokenForCurrentUser()
    {
        $token = JWTAuth::getToken();
        return UserValidToken::where('token', '=' , $token)->first();
    }

    public function checkTokenValidity()
    {

        if(self::getValidTokenForCurrentUser() == null)
        {
            return [
                'is_user'=>'false'
            ];
        }


        try {
            $user = JWTAuth::parsetoken()->authenticate();
            return [
                'name'=> $user->name,
                'type'  => strtolower(substr($user->userable_type, 4)),
                'is_user' =>'true'
            ];

        } catch (\Exception $e){
            return [
                'is_user'=>'false'
            ];
        }
    }
}