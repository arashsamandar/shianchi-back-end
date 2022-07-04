<?php

namespace App\Http\Controllers;

use App\Permission;
use App\Product;
use App\RejectionMessage;
use Illuminate\Http\Request;
use App\Http\Requests;
use Wego\UserHandle\UserPermission;

class RejectionMessageController extends ApiController
{
    public function setToRead(Request $request){
        UserPermission::checkPermission([Permission::VERIFY_PRODUCT]);
        $messageId = $request->input('message_id');
        RejectionMessage::where('id',$messageId)->update([
            'is_read' => '1',
        ]);
        return $this->respondOk('message is set to read');
    }

    public function getMessage(Request $request){
        $productId = $request->input('product_id');
        return Product::where('product_id',$productId)->where('is_read','0')->first()->toArray();
    }
}
