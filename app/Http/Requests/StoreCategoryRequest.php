<?php

namespace App\Http\Requests;

use App\Category;
use Dingo\Api\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
        $rules = [];
        if ($this->requestHasModifiedCategory()) {
            $rules = $this->englishNameRules();
        }

        $rules += [
            'modify.*.persian_name' => 'required|alpha_spaces',
            'modify.*.category_id' => 'required',
            'modify.*.is_leaf' => 'required|in:true,false',
            'modify.*.id' => 'required',
            'modify.*.english_path' => 'required',
            'modify.*.unit' => 'required',
            'delete.*' => 'numeric'
        ];
        return $rules;
    }

    public function englishNameRuleByCategoryId($category_id)
    {

        if ($this->isNew($category_id)) {

            return 'required|alpha_dash|unique:categories,name,NULL,id,deleted_at,NULL';

        }
        if (Category::find($category_id)->isLeaf == 1) {
            return 'required|alpha_dash';
        } else {
            return 'required|alpha_dash|exists:categories,name,id,' . $category_id;
        }


    }

    /**
     * @param $returnArray
     * @return mixed
     */
    private function englishNameRules()
    {
        $returnArray = [];

        foreach ($this->get('modify') as $key => $modify) {

            $returnArray['modify.' . $key . '.english_name'] = $this->englishNameRuleByCategoryId($modify['category_id']);

        }

        return $returnArray;
    }

    /**
     * @param $category_id
     * @return bool
     */
    private function isNew($category_id)
    {
        return !strcmp($category_id, '#');
    }

    /**
     * @return bool
     */
    private function requestHasModifiedCategory()
    {
        return array_key_exists('modify', $this->request->all());
    }

    public function messages()
    {
        $returnArray = [];
        if (is_null($this->get('modify')))
            return [];
        foreach ($this->request->all()['modify'] as $key => $modify) {
            if ($this->isPersianNameExist($key))
                $returnArray[$this->getExistsEnglishNameIndex($key)] = $this->getExistsEnglishNameMessage($key);
            if (($this->isEnglishNameExist($key) && $this->isPersianNameExist($key)))
                $returnArray[$this->getUniqueEnglishNameIndex($key)] = $this->getUniqueEnglishNameMessage($key);
        }
        return $returnArray;
    }

    /**
     * @param $key
     * @return bool
     */
    private function isPersianNameExist($key)
    {
        return isset($this->request->all()['modify'][$key]['persian_name']);
    }

    /**
     * @param $key
     * @return bool
     */
    private function isEnglishNameExist($key)
    {
        return isset($this->request->all()['modify'][$key]['english_name']);
    }

    /**
     * @param $key
     * @return string
     */
    private function getExistsEnglishNameIndex($key)
    {
        return 'modify.' . $key . '.english_name.exists';
    }

    /**
     * @param $key
     * @return string
     */
    private function getExistsEnglishNameMessage($key)
    {
        return 'نام انگلیسی ' . $this->request->all()['modify'][$key]['persian_name'] . ' قابل تغییر نیست';
    }

    /**
     * @param $key
     * @return string
     */
    private function getUniqueEnglishNameIndex($key)
    {
        return 'modify.' . $key . '.english_name.unique';
    }

    /**
     * @param $key
     * @return string
     */
    private function getUniqueEnglishNameMessage($key)
    {
        return 'نام انگلیسی ' . $this->request->all()['modify'][$key]['persian_name'] . ' قبلا وارد شده است';
    }


}

