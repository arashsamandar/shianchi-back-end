<?php

namespace App\Http\Requests\comments;

use App\Http\Controllers\ApiController;
use App\Http\Requests\ApiResponse;
use App\Http\Requests\Request;
use Dingo\Api\Http\FormRequest;

class SetNotConfirmedCommentRequest extends FormRequest
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
            'message'=>'required'
        ];
    }
}
