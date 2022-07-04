<?php

namespace App\Http\Requests\store;

use App\Http\Requests\ApiResponse;
use Dingo\Api\Http\FormRequest;

class UpdateStorePhoneRequest extends FormRequest
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
            'fax_number'=>'numeric|digits_between:10,12',
            'phones.*.prefix_phone_number'=>'digits_between:3,4',
            'phones.*.phone_number'=>'digits_between:7,8',
            'password' => 'confirmed'
        ];
    }
}
