<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/21/16
 * Time: 11:05 AM
 */

namespace Wego\Store;

use App\Repositories\StoreRepository;
use App\Role;
use App\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Wego\PictureHandler;


class StoreFactory
{
    protected $request, $storeId, $staff;
    protected $storeRepository;


    // TODO : argument $user chera inja hich kari nemikone ?
    /**
     * @param Request $request
     * @param $user
     */
    public function save(Request $request, $user)
    {
        $this->staff = $user;
        $this->request = $request;
        DB::transaction(function () {
            $saveStore = new SaveStore;
            $store = $saveStore->save($this->request->all());
            $storeRoleId = Role::getRoleId(Role::STORE);
            $store->user->roles()->attach([$storeRoleId]);
            $this->storeId = $store->id;

            (new PhoneHandler)->save($this->request->input('phone'), $store);


            (new ManagerMobileHandler)->save($this->request->input('manager_mobile'), $store);
            if ($this->request->input('departments') !== null)
                (new DepartmentHandler)->save($this->request->input('departments'), $store);

            (new WorkTimeHandler)->save($this->request->input('work_time'), $store);

            (new PictureHandler())->storePicture($this->request->input('pictures'), $store, $this->staff);

            $this->saveToElasticSearch($this->storeId);
        });

    }

    /**
     * @param array $request
     * @param $id
     */
    public function update(array $request, $id)
    {
        Store::where('id', '=', $id)->update($request);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function saveToElasticSearch($storeId)
    {
        Store::where('id', '=', $storeId)->elastic()->addToIndex();
        return true;
    }
}