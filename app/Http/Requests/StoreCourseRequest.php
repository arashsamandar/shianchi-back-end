<?php

namespace App\Http\Requests;


use Dingo\Api\Http\FormRequest;

class StoreCourseRequest extends FormRequest
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
            "courses"=>'required|array',
            "participant.email"=>'required|email',
            "participant.name"=>'required|alpha_spaces',
            "participant.phone_number"=>'required|digits_between:10,11',
            "participant.type"=>'required|numeric|between:0,2',

        ];
    }
}
