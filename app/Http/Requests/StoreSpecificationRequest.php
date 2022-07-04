<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest;

class StoreSpecificationRequest extends FormRequest
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
            "data.*.name" => "required",
            "data.*.is_text_field" => "required|boolean",
            "data.*.important" => "required|boolean",
            "data.*.for_buy" => "required|boolean",
            "data.*.multi_value" => "required|boolean",
            "data.*.searchable" => "required|boolean",
            "data.*.category_id" => "required|numeric",
            "data.*.title_id" => "required|numeric"
        ];
    }
}
