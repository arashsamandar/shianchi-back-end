<?php

namespace App\Http\Requests;


use Dingo\Api\Http\FormRequest;

class StoreProductReportRequest extends FormRequest
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
            'body' => 'required',
            'type' => 'required',
            'reported_product_id' =>'required'
        ];
    }
}
