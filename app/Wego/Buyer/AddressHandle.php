<?php
use App\BuyerAddress;
use App\Buyer;
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 3/5/16
 * Time: 7:14 PM
 */
class AddressHandle
{
    public function save(array $requests, Buyer $buyer)
    {
        if(count($requests) > 5)
            return;
        foreach($requests as $request){
            $buyer->buyer_addresses()->create($request);
        }
    }
}