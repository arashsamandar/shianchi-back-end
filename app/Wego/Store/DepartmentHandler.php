<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/24/16
 * Time: 1:47 PM
 */

namespace Wego\Store;

use App\Store;
use Wego\Helpers\JsonUtil;
use Wego\PictureHandler;

class DepartmentHandler implements StorePivotHandlerInterface
{

    public function save(array $requests, Store $store)
    {
        if(count($requests) > 10)
            return;
        foreach ($requests as &$request) {
            $request = JsonUtil::convertKeys($request,[
                'department_manager_picture' => 'path',
                'department_id' => 'id'

            ]);
            $request['department_manager_picture'] = $this->saveDepartmentManagerPicture($request['department_manager_picture'],$store);
            unset($request['title']);
            unset($request['image']);
        }
        unset($request);
        $requestById = array_combine(array_column($requests,'department_id'), array_values($requests));
        $store->departments()->attach($requestById);
    }
    
    public function get(Store $store)
    {
        return $store->departments();
    }
    private function saveDepartmentManagerPicture($tempPicPath,$store){
        return ((new PictureHandler())->moveAllPictures($tempPicPath,$store,"Store"));
    }
}