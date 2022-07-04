<?php

namespace App\Http\Requests;


use Dingo\Api\Http\FormRequest;

class ContactUsRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required|email|max:50',
            'g-recaptcha-response' => 'required'
        ];
    }
}
