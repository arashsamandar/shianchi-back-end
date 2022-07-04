<?php

use App\User;
use App\Role;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach(array_keys(Role::$roles) as $role){
            if(Role::where('name','=',$role)->first() == null){
                Role::create(['name' => $role ]);
            }
        }
        self::attachRolesToUsers();
    }

    public static function attachRolesToUsers(){
        $users = User::all();
        foreach($users as $user){
            if ($user->userable_type == \Wego\UserHandle\UserPermission::STORE){
                $storeRoleId = Role::where('name','=',Role::STORE)->first()->id;
                $user->attachRole($storeRoleId);
                $user->save();
            } elseif ($user->userable_type == \Wego\UserHandle\UserPermission::BUYER){
                $storeRoleId = Role::where('name','=',Role::BUYER)->first()->id;
                $user->attachRole($storeRoleId);
                $user->save();
            } elseif ($user->userable_type == \Wego\UserHandle\UserPermission::STAFF){
                $storeRoleId = Role::where('name','=',Role::LOCAL_STAFF)->first()->id;
                $user->attachRole($storeRoleId);
                $user->save();
            }
        }
    }
    private static function addPermissionToLocalStaff()
    {
        $staffRoleId = Role::where('name','=',Role::LOCAL_STAFF)->first()->id;

        foreach (StaffTableSeeder::$localStaffsEmail as $email) {
            $staff = User::where('email',$email)->first()->userable;
            $staff->user->attachRole($staffRoleId);
            $staff->save();
        }
    }
}
