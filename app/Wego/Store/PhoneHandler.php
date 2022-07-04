<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/20/16
 * Time: 6:48 PM
 */

namespace Wego\Store;


use App\Store;
use App\StorePhone;

class PhoneHandler implements StorePivotHandlerInterface
{

    public function save(array $requests,Store $store)
    {
        $phoneArray =[];
        if(count($requests) > 10)
            return;
        $count = 1;
        foreach ( $requests as $request){
            $phoneArray[] = new StorePhone([
                'type'=> $count,
                'prefix_phone_number'=>$request['prefix_phone_number'],
                'phone_number'=>$request['phone_number'],
            ]);
            $count = 0;
        }
        $store->phones()->saveMany($phoneArray);
    }
}