<?php

namespace App\Http\Controllers;

use App\BuyerAddress;
use App\Order;
use App\Product;
use App\User;
use Dingo\Api\Routing\Helpers;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Mockery\CountValidator\Exception;
use Wego\Search\FindById;
use Wego\Transforms\ProductElasticSearchTransformer;
use Wego\Transforms\ProductTransformer;
use Wego\Transforms\UserTransformer;
use Wego\UserHandle\UserPermission;

class UserController extends ApiController
{
    use Helpers;
    public function addToWatchlist(Request $request){
        $user = $this->auth->user();
        $productId = $request->input('product_id');
        if(DB::table('stalker_user')->where('product_id',$productId)->where('user_id',$user->id)->first()=== null){
            $user->target_products()->attach($productId);
        }
        return $this->setStatusCode(200)->respondOk('product added successfully');
    }

    public function checkBuyerPermission()
    {
        return UserPermission::checkBuyerPermission();
    }

    public function getUserByOderId($orderId)
    {
        $orderId = decrypt($orderId);
        $user = Order::find($orderId)->user;
        $userPermission = new UserPermission();
        $userPermission->setUser($user);
        if($user != null && $userPermission->hasBuyerAbility())
            return $user;
        return null;
    }

    public function getMobileByOrderId($orderId)
    {
        $orderId = decrypt($orderId);
        $order = Order::find($orderId);
        $addressId = $order->address_id;
        $buyerAddress = BuyerAddress::find($addressId);
        $mobileNum = $buyerAddress->prefix_mobile_number . $buyerAddress->mobile_number;
        return $mobileNum;
    }
}
