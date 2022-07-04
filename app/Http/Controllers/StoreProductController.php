<?php

namespace App\Http\Controllers;

use App\Events\ProductSetToExist;
use App\Http\Requests\AttachProductToStoreRequest;
use App\Http\Requests\EditStoreProductRequest;
use App\Product;
use App\ProductDetail;
use App\SpecialCondition;
use App\Store;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Wego\Product\ProductEditor;
use Wego\Product\ProductFactory;

class StoreProductController extends ApiController
{
    use Helpers;
    public function store(AttachProductToStoreRequest $request)
    {
        $details = $request->all();
        $storeId = $this->auth->user()->userable_id;
        $details['store_id'] = $storeId;
        $detail = ProductDetail::create($details);
        if($detail['quantity']>0){
            $existingDetails = ProductDetail::where('product_id',$request->product_id)->where('quantity','>',0)->first();
            if(empty($existingDetails)) {
                event(new ProductSetToExist($request->product_id));
            }
        }
        if (isset($request['special']))
            (new ProductFactory())->saveSpecial($request['special'],$detail);
        Product::where('id',$request->product_id)->elastic()->addToIndex();
        $store = Store::find($storeId);
        $product = Product::find($request->product_id);
        $store->categories()->sync([$product->category_id], false);
        return $this->respondOk('added successfully');
    }

    public function update(EditStoreProductRequest $request)
    {
        $data = $request->all();
        if (empty($data['color_id'])){
           $data = array_except($data,'color_id');
        }
        try{
            $detail = ProductDetail::find($request->id);
            if($data['quantity'] >0 && $detail->quantity==0){
                $details = ProductDetail::where('product_id',$request->product_id)->where('quantity','>',0)->first();
                if(empty($details)) {
                    event(new ProductSetToExist($request->product_id));
                }
            }
            ProductDetail::where('id',$request->id)->update(array_except($data,['special','token']));
            if (isset($request['special']))
                (new ProductEditor())->updateSpecialConditions($request->all(), $detail);
            else
                SpecialCondition::where('product_detail_id', $request->id)->where('status', SpecialCondition::AVAILABLE)->delete();
            Product::where('id', $request->product_id)->elastic()->addToIndex();
        } catch (\Exception $e){
            dd($e->getMessage());
        }
        return $this->respondOk('updated successfully');
    }

    public function delete($id)
    {
        $detail = ProductDetail::findOrFail($id);
        $productId = $detail->product_id;
        $detail->delete();
        Product::where('id',$productId)->elastic()->addToIndex();
        return $this->respondOk('deleted successfully');
    }
}
