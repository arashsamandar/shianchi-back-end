<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest;

class StoreProductPictureRequest extends FormRequest
{
    use ApiResponse;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'pic'=>'required|image:png,jpg'
        ];
    }
}
