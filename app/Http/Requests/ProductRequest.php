<?php

namespace App\Http\Requests;


use Dingo\Api\Http\FormRequest;

class ProductRequest extends FormRequest
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
        $rules = $this->addDiscountValidation();
        $rules +=
            [
//                "english_name"      => "required|between:5,150",
                "persian_name"      => "required|between:5,150",
                "key_name"          => "between:0,255",
//                "current_price"     => "required|numeric|digits_between:3,8",
//                "description"       => "required|between:0,4000",
                "weight"            => "required|numeric|digits_between:1,7",
//                "wego_coin_need"    => "required|numeric|digits_between:0,3",
//                "quantity"          => "required|numeric|digits_between:1,3",
//                "special"=>"array|special",
                "pictures" => "required|array",
                "values" => "required",
                "category_id" => "required|numeric",
//                "colors" => "array",
//                "warranty_name" => "present|between:5,100",
//                "warranty_text" => "present|between:0,200",
//                "height" => "required",
//                "width" => "required",
//                "length" => "required"
            ];
        return $rules;
    }

    private function addDiscountValidation()
    {
        if (array_key_exists('special', $this->request->all())){
            foreach ($this->get('special') as $key=>$special){
                if ($special['type'] == 'discount'){
                    return ['special.'.$key.'.amount' => 'numeric|max:'.$this->get('current_price')];
                }
            }
        };
        return [];
    }
}
