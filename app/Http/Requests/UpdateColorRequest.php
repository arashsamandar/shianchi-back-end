<?php

namespace App\Http\Requests;

use App\Http\Requests;
use Dingo\Api\Http\FormRequest;
use Illuminate\Validation\Rule;

//use Illuminate\Support\Facades\Validator;


class UpdateColorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    use ApiResponse;

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

        $id = $this->request->all()['id'];

        return [
            'english_name' => ['required', 'alpha_spaces', 'unique:colors,english_name,' . $id],
            'persian_name' => ['required', 'alpha_spaces', 'unique:colors,persian_name,' . $id],
            'code' => ['required', 'unique:colors,code,' . $id],
        ];

    }
}
