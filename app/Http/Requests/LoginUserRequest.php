<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest;

class LoginUserRequest extends FormRequest
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
            "email"=>"required|email",
            "password"=>"required"
        ];
    }
}
