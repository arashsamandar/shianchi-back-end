<?php

namespace App\Http\Requests;


use App\ProductDetail;
use Dingo\Api\Http\FormRequest;

class EditStoreProductRequest extends FormRequest
{
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
        $array = ['value_id','color_id'];
        $notExistArray = [];
        foreach($array as $field){
            if(!array_key_exists($field,$this->request->all())){
                array_push($notExistArray,$field);
            }
        }
        $nArray = array_diff($array,$notExistArray);
        $nArray = array_values($nArray);
        $returnArray = [];
        if (count($nArray)==1) {
            $returnArray[$nArray[0]] = 'unique:product_details,' . $nArray[0] . ','.$this->get('id').',id,store_id,' . $this->get('store_id') .
                ',product_id,' . $this->get('product_id');

        } elseif (count($nArray)==2){
            $returnArray[$nArray[0]] = 'unique:product_details,' . $nArray[0] . ','.$this->get('id').',id,store_id,' . $this->get('store_id') .
                ',product_id,' . $this->get('product_id').','.$nArray[1].','.$this->get($nArray[1]);

        }
        if (empty($nArray)){
            $returnArray['store_id'] = 'unique:product_details,store_id,'.$this->get('id').',id,product_id,'.$this->get('product_id');
        }
        $details = ProductDetail::where('product_id',$this->get('product_id'))->get();
        $detailCount = count($details);
        $hasColor = false;
        foreach($details as $detail){
            if(!empty($detail->color_id)){
                $hasColor = true;
                break;
            }
        }
        if($hasColor){
            $returnArray['color_id'] = 'required';
        } elseif($detailCount != 0) {
            $returnArray['color_id'] = (empty($this->color_id)) ? 'present' : 'alpha';
        }
        $returnArray += [
            'warranty_id'=>'required',
            'product_id'=>'required',
            'current_price'=>'required',
            'quantity'=>'required'
        ];
        return $returnArray;
    }
}
