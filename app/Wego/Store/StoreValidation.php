<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/20/16
 * Time: 5:38 PM
 */

namespace Wego\Store;
use Wego\UserHandle\Validateable;
use Illuminate\Support\Facades\Validator;

class StoreValidation implements Validateable
{

    public function validate($request)
    {
        return Validator::make($request,
            [
                "persian_name" => "required|between:2,20",
                "english_name" =>"required|between:2,20",
                "email" => "bail|required|email|unique:users,email",
                "password" => "required|confirmed",
                "business_license"=> "present|numeric",
                "province_id"=>"required",
                "city_id"=>"required",
                "bazaar"=>"required",
                "address"=>"required",
                "shaba_number"=>"present",
                "fax_number"=>"required|numeric",
                "information"=>"required|between:10,100",
                "account_number"=>"required",
                "card_number"=>"present",
                "card_owner_name"=>"present",
                "manager_first_name"=>"required|between:2,20",
                "manager_last_name"=>"required|between:2,20",
                "manager_national_code" => "required|digits:10",
                "manager_picture"=>"present",
                "wego_expiration"=>"required|numeric",
                "location"=>"required|array",
                "manager_mobile"=>"present|array",
                "departments"=>"array",
                "work_time"=>"present|array",
                "phone"=>"present|array",
                "about_us"=>"required|between:5,2000",
                "pictures"=>"present|array"
                
            ]
        );
    }
}