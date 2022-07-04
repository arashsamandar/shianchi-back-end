<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest;

class UpdateBuyerRequest extends FormRequest
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
            "card_number" => "card",
            "mobile_number"=>"numeric",
            "landline_number"=>"numeric",
            "card_owner_name"=>"string",
            "image_path"=>"string",
            "birthday"=>"string",
            "job_title"=>"string",
            "company_name"=>"string",
        ];
    }
}
