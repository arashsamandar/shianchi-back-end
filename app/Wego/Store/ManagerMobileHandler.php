<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/24/16
 * Time: 1:00 PM
 */

namespace Wego\Store;


use App\ManagerMobile;
use App\Store;

class ManagerMobileHandler implements StorePivotHandlerInterface
{

    public function save(array $requests, Store $store)
    {
        $mobileArray = [];
        if(count($requests) > 10)
            return;
        foreach ($requests as $request){
            $mobileArray[] = new ManagerMobile($this->mapToDatabase($request));
        }
        $store->manager_mobiles()->saveMany($mobileArray);

    }

    private function mapToDatabase($request)
    {
        return [
            'prefix_phone_number'=> $request['prefix_phone_number'],
            'phone_number' => $request['phone_number']
        ];
    }
}