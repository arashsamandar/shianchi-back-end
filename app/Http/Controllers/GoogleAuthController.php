<?php

namespace App\Http\Controllers;

use App\Buyer;
use App\UserValidToken;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Wego\GenerateToken;

class GoogleAuthController extends Controller
{
    public function login(Request $request)
    {
        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
            // Ignores notices and reports all other kinds... and warnings
            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
            //     error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
        }
        return $this->execute($request->has('code'),$request->url());
    }

    private function execute($hasCode,$url)
    {
        if (!$hasCode)
            return $this->getAuthorizationFirst();
        $user = Socialite::with('google')->stateless()->user();
        $user = $this->findOrCreate($user);
        $token = JWTAuth::fromUser($user);
        UserValidToken::create(['user_email' => $user->email , 'token' => $token]);
        return redirect($this->generateRedirectUrl($user,$token));

    }

    private function getAuthorizationFirst()
    {
        return Socialite::with('google')->stateless()->redirect();
    }

    private function findOrCreate($googleUser)
    {
        $user = \App\User::firstOrCreate(['email' => $googleUser->email]);
        if (empty($user->name)){
            $user->name=$googleUser->name;
            $user->save();
        }
        if (empty($user->userable_type)){
            $buyer = Buyer::create();
            $buyer->user()->save($user);
            $buyer->user->attachRole(4);
        }
        return $user;

    }

    private function generateRedirectUrl($user,$token)
    {
        return 'http://shiii.ir/authentication?name='.$user->name.'&token='.$token.'&type='.strtolower(substr($user->userable_type,4));
    }


}
