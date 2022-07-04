<?php

namespace App\Http\Requests;


use App\ProductDetail;
use Dingo\Api\Http\FormRequest;
use Tymon\JWTAuth\Facades\JWTAuth;

class AttachProductToStoreRequest extends FormRequest
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
        $user = JWTAuth::parsetoken()->authenticate();
        $this->request->add(['store_id'=> $user->userable_id]);
        if (empty($this->get('color_id'))){
            $this->request->remove('color_id');
        }
        $array = ['value_id','color_id'];
        $nArray = [];
        foreach($array as $field){
            if(array_key_exists($field,$this->request->all())){
                array_push($nArray,$field);
            }
        }
        $returnArray = [];
        if (count($nArray)==1) {
                $returnArray[$nArray[0]] = 'unique:product_details,' . $nArray[0] . ',NULL,id,store_id,' . $this->get('store_id') .
                    ',product_id,' . $this->get('product_id').',warranty_id,'.$this->get('warranty_id').',deleted_at,NULL';

        } elseif (count($nArray)==2){
                $returnArray[$nArray[0]] = 'unique:product_details,' . $nArray[0] . ',NULL,id,store_id,' . $this->get('store_id') .
                    ',product_id,' . $this->get('product_id').',warranty_id,'.$this->get('warranty_id').
                    ','.$nArray[1].','.$this->get($nArray[1]).',deleted_at,NULL';

        }
        if (empty($nArray)){
            $returnArray['product_id'] = 'unique:product_details,product_id,NULL,id,store_id,'.$this->get('store_id').
                ',warranty_id,'.$this->get('warranty_id').',deleted_at,NULL';
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
