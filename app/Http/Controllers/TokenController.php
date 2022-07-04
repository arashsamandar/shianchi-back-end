<?php

namespace App\Http\Controllers;

use App\UserValidToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokenController extends ApiController
{
    public function invalidThisToken(Request $request)
    {
        $token =  $request->input('token');
        JWTAuth::setToken($token)->invalidate();
        UserValidToken::where('token','=',$token)->delete();
        return $this->respondOk();
    }

    public static function deleteExpireTokenFromDB()
    {
        $allUserValidTokens = UserValidToken::all();
        foreach($allUserValidTokens as $userValidToken)
        {
            if(self::isTokenExpire($userValidToken))
            {
                UserValidToken::where('user_email','=',$userValidToken->user_email)->delete();
            }
        }
    }

    private static function diffInSecondsBetweenUserValidTokenCreateAndNow($userValidToken)
    {
        return $userValidToken->created_at->diffInSeconds(Carbon::now());
    }

    private static function isTokenExpire($userValidToken)
    {
        if(self::diffInSecondsBetweenUserValidTokenCreateAndNow($userValidToken) > env('TOKEN_EXPIRATION',60000))
        {
            return true;
        }
        return false;
    }
}
