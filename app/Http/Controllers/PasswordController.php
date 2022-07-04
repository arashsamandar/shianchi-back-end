<?php

namespace App\Http\Controllers;

use App\Http\Requests\resetPassword\ResetPasswordRequest;
use App\Http\Requests\resetPassword\ResetPasswordTokenRequest;
use App\Http\Requests\resetPassword\SendResetPasswordMailRequest;
use App\Jobs\SendResetEmail;
use App\PasswordChangeRequest;
use App\User;
use App\UserValidToken;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Requests;
use Illuminate\Support\Facades\Lang;
use Tymon\JWTAuth\Facades\JWTAuth;


class PasswordController extends ApiController
{
    use DispatchesJobs;

    /**
     * Create a new password controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * checks if user exists if so insert request in DB and sends an reset email to user's email
     * @param SendResetPasswordMailRequest $request
     * @return mixed
     */
    public function sendMailOfResetPassword(SendResetPasswordMailRequest $request){
        $email = $request->input('email');
        $user = User::where('email','=',$email)->first();
        if($user === null){
            return $this->setStatusCode(404)->respondWithError(Lang::get('generalMessage.UserNotFound'));
        }
        $token = $this->insertResetRequest($user->email);
        $this->sendResetMail($token,$user->email);
        return $this->respondOk('email will be sent soon');
    }

    /**
     * check Reset entry and reset the password
     * @param ResetPasswordRequest $request
     * @return mixed
     */
    public function resetPasswordRequest(ResetPasswordRequest $request){
        $token = $request->input('token');
        $changeRequest = PasswordChangeRequest::where('token','=',$token)->first();
        if($changeRequest != null && !$changeRequest->isOld()){
            self::deleteAllValidToken($token);
            $this->resetPassword($token,$request->input('password'));
            return $this->respondOk(Lang::get('generalMessage.ForgotEmailSuccessful'));
        }
        return $this->respondWithError(Lang::get('generalMessage.ForgotEmailUnSuccessful'));
    }



    private static function deleteAllValidToken($token)
    {
        $email = PasswordChangeRequest::where('token','=',$token)->first()->email;
        $allValidTokens = UserValidToken::where('user_email','=',$email)->get();
        foreach($allValidTokens as $ValidToken)
        {
            JWTAuth::setToken($ValidToken->token)->invalidate();
        }
        UserValidToken::where('user_email','=',$email)->delete();
    }

    /**
     * @param ResetPasswordTokenRequest $request
     * @return mixed
     */
    public function checkTokenValidate(ResetPasswordTokenRequest $request){
        $token = $request->input('token');
        $changeRequest = PasswordChangeRequest::where('token','=',$token)->first();
        if($changeRequest != null){
            return $this->respondOk();
        }
        return $this->respondWithError("token invalid or request expired");
    }

    /**
     * delete all expired reset requests
     */
    public static function cleanPasswordChangeRequestsTable(){
        $expirationDay = Carbon::now()->subDay(PasswordChangeRequest::VALIDITY_DURATION);
        PasswordChangeRequest::where('date', '<' , $expirationDay)->delete();
    }

    /**
     * Reset user's password
     * @param $token
     * @param $password
     */
    private function resetPassword($token, $password){
        $email = PasswordChangeRequest::where('token','=',$token)->first()->email;
        PasswordChangeRequest::where('token','=',$token)->delete();
        $user = User::where('email','=',$email)->first();
        $user->password = bcrypt($password);
        $user->save();
    }

    /**
     * insert reset request into DB
     * @param $userEmail
     * @return string
     */
    private function insertResetRequest($userEmail){
        $randomToken = random_bytes(30);
        $token = bin2hex($randomToken);
        (new PasswordChangeRequest())->create([
            'token' => $token,
            'email' => $userEmail,
            'date' => Carbon::now(),
        ]);
        return $token;
    }

    /**
     * sends reset mail to users's email
     * @param $token
     * @param $userMail
     */
    private function sendResetMail($token,$userMail){
        $sendResetEmail = new SendResetEmail();
        $sendResetEmail->setTo($userMail)->setToken($token);
        $this->dispatch($sendResetEmail);
    }
}
