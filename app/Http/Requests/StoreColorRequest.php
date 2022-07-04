<?php

namespace App\Http\Requests;


use Dingo\Api\Http\FormRequest;

class StoreColorRequest extends FormRequest
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
            'english_name' => 'required|alpha_spaces|unique:colors,english_name' ,
            'persian_name' => 'required|alpha_spaces|unique:colors,persian_name' ,
            'code' => 'required|unique:colors,code'
        ];
    }
}
