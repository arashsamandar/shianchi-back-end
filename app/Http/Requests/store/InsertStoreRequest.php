<?php

namespace App\Http\Requests\store;

use App\Http\Requests\ApiResponse;
use App\Http\Requests\Request;
use Dingo\Api\Http\FormRequest;

class InsertStoreRequest extends FormRequest
{
    use ApiResponse;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "persian_name" => "required|between:2,40",
            "english_name" =>"required|between:2,40",
            "email" => "bail|required|email|unique:users,email|between:2,40",
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
            "manager_first_name"=>"required|between:2,40",
            "manager_last_name"=>"required|between:2,40",
            "manager_national_code" => "digits:10",
            "manager_picture"=>"present",
            "wego_expiration"=>"required|numeric",
            "location"=>"required|array",
            "manager_mobile"=>"present|array",
            "departments"=>"array",
            "departments.*.department_manager_first_name"=>"between:2,40",
            "departments.*.department_manager_last_name"=>"between:2,40",
            "work_time"=>"present|array",
            "phone"=>"present|array",
            "about_us"=>"required|between:0,2000",
            "pictures"=>"present|array"
        ];
    }
}
