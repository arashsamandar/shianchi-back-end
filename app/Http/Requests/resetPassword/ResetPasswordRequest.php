<?php

namespace App\Http\Requests\resetPassword;
use App\Http\Requests\ApiResponse;
use App\Http\Requests\Request;
use Dingo\Api\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            "token" => "required",
            "password" => "required|confirmed",
        ];
    }
}