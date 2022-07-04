<?php

namespace App\Http\Requests;


use Dingo\Api\Http\FormRequest;

class UpdateBazaarRequest extends FormRequest
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

        $id = $this->request->all()['id'];

        return [
            'name'          => ['required','unique:bazaars,name,'.$id],
            'address'       => 'required',
            'city_id'       => 'required',
            'province_id'   => 'required'

        ];
    }
}
