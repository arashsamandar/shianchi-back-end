<?php


use App\Permission;
use App\Role;
use Illuminate\Database\Seeder;

class PermissionRoleTableSeeder extends Seeder
{
    public function run()
    {
        foreach(array_keys(Role::$roles) as $roleName){
            $role = Role::where('name' ,'=' ,$roleName)->first();
            $permissionsIds = $a = Permission::whereIn('name', Role::$roles[$roleName])->get();
            if(count($permissionsIds) > 0)
                foreach ($permissionsIds as $permissionsId){
                    $role->attachPermission($permissionsId->id);
                }
        }
    }
}
