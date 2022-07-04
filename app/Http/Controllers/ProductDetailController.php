<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachProductToStoreRequest;
use App\Product;
use App\ProductDetail;
use App\SpecialCondition;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

class ProductDetailController extends ApiController
{
    use Helpers;
    protected $optionalFields = ['value_id','warranty_id','color_id'];
    public function show(Request $request)
    {
        $details = null;
        $productDetails = ProductDetail::where('product_id',$request->product_id)->where('color_id',$request->color_id)
            ->with(['special_conditions'=>function($query){
                $query->where('status',SpecialCondition::AVAILABLE);
            },'store'=>function($query){
                $query->with(['user'=>function($query){
                    $query->select(['users.userable_id','users.name']);
                }])->select(['stores.id']);
            },'warranty'=>function($query){
                $query->select(['warranties.warranty_name','warranties.id']);
            }])->get();
        $filteredDetail = array_filter($productDetails->toArray(), function ($detail) {
            return ($detail['quantity'] > 0);
        });
        $filteredDetail = array_values($filteredDetail);
        foreach ($filteredDetail as &$fDetail) {
            $discount = array_filter($fDetail['special_conditions'], function ($special) {
                return ($special['type'] == 'discount');
            });
            $discount = array_values($discount);
            $fDetail['second_price'] = $fDetail['current_price'];
            if (!empty($discount)) {
                $fDetail['second_price'] = $fDetail['current_price'] - $discount[0]['amount'];
            }
        }
        $items = [];
        if(!empty($filteredDetail)) {
            $details = null;
            $index = array_search(min(array_column($filteredDetail, 'second_price')), array_column($filteredDetail, 'second_price'));
            $items[] = $filteredDetail[$index];
        }
        $details = array_sort($productDetails->toArray(),function($value){
            return $value['current_price'];
        });
        $details = array_values($details);
        $items = array_merge($items,$details);
        $unique_array = null;
        foreach($items as $element) {
            $hash = $element['warranty_id'];
            if(!isset($unique_array[$hash])){
                $unique_array[$hash] = $element;
            };
        }
        $details = array_values($unique_array);
        return $details;
    }

    public function store(AttachProductToStoreRequest $request)
    {
        $details = $request->all();
        ProductDetail::create($details);
        return $this->respondOk();
    }

    public function getProductDetails($id)
    {
        $store = $this->auth->user();
        $productName = Product::find($id)->persian_name;
        $details = ProductDetail::where('store_id',$store->userable_id)->where('product_id',$id)
            ->with(['special_conditions'=>function($query){
                $query->where('status',SpecialCondition::AVAILABLE);
            },'store'=>function($query){
            $query->with(['user'=>function($query){
                $query->select(['users.userable_id','users.name']);
            }])->select(['stores.id']);
        },'warranty'=>function($query){
            $query->select(['warranties.warranty_name','warranties.id']);
        },'color'=>function($query){
             $query->select(['colors.id','colors.persian_name','colors.code']);
        }])->get()->toArray();
        foreach($details as &$detail){
            $this->changeSpecialExpirationValue($detail['special_conditions']);
        }
        $array = [];
        $array['product_details'] = $details;
        $array['product_name'] = $productName;
        return $array;
    }
    private function changeSpecialExpirationValue(&$specialConditions)
    {
        foreach ($specialConditions as &$condition) {
            $expirationDate = Carbon::parse($condition['expiration']);
            $diffDays = Carbon::now()->setTime(0, 0)->diffInDays($expirationDate, false);
            $condition['expiration'] = $diffDays < 0 ? 0 : $diffDays;
        }
    }

    public function setUid()
    {
        $id = request()->id;
        $uid = request()->uid;
        ProductDetail::where('id',$id)->update(['uid'=>$uid]);
        return $this->respondOk();
    }
}
