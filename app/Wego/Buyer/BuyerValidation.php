<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/19/16
 * Time: 3:55 PM
 */

namespace Wego\Buyer;
use Illuminate\Support\Facades\Validator;

use Wego\UserHandle\Validateable;

class BuyerValidation implements Validateable
{
    public function validate($request)
    {
        //dd($request);
        return Validator::make($request,
            [
                'name' =>'required|alpha_spaces|max:50',
                'last_name' => 'required|alpha_spaces|max:50',
                'email' => 'required|email|unique:users|max:50',
                'g-recaptcha-response' => 'required',
                'mobile_number' => 'required',
                'national_code' => 'required|digits:10',
                'password' => 'required|confirmed',
            ]
        );
    }
}