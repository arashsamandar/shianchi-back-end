<?php

namespace App\Http\Requests;


use Dingo\Api\Http\FormRequest;

class StoreBazaarRequest extends FormRequest
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
            'name'          => 'required|unique:bazaars,name' ,
            'address'       => 'required',
            'city_id'       => 'required',
            'province_id'   => 'required'

        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'لطفا نام بازار را وارد نمایید' ,
        ];
    }
}
