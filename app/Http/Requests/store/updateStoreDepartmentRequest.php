<?php

namespace App\Http\Requests\store;

use App\Http\Requests\ApiResponse;
use App\Http\Requests\Request;
use Dingo\Api\Http\FormRequest;

class updateStoreDepartmentRequest extends FormRequest
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
            'departments'=>'required|array',
            'departments.*.department_manager_first_name'=>'required|alpha_spaces',
            'departments.*.department_id'=>'required|numeric',
            'departments.*.department_prefix_phone_number'=>'required|numeric',
            'departments.*.department_phone_number'=>'required|numeric',
            'departments.*.department_email'=>'required|email',
            'departments.*.department_manager_picture'=>'required',
        ];
    }
}
