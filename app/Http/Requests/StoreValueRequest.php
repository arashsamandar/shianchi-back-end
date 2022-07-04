<?php

namespace App\Http\Requests;


use Dingo\Api\Http\FormRequest;

class StoreValueRequest extends FormRequest
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
            "data" => "required",
            "data.*.specification_id" => "required",
            "data.*.name" => "required"
        ];
    }
}
