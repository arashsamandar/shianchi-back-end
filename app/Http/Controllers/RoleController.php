<?php

namespace App\Http\Controllers;

use App\Permission;
use App\Role;
use App\Staff;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Intervention\Image\Exception\NotFoundException;
use Wego\UserHandle\UserPermission;

class RoleController extends ApiController
{
    public function getAllRoles()
    {
        $roles = Role::all();
        return $roles->toArray();
    }

    public function getAllPermissions()
    {
        $permissions = Permission::all();
        return $permissions->toArray();
    }

    public function storeRole(Request $request)
    {
        $role = Role::create($request->all());
        return $this->respondOk($role->id);
    }

    public function storePermission(Request $request)
    {
        $perm = Permission::create($request->all());
        return $this->respondOk($perm->id);
    }

    public function addPermissionsToRole(Request $request)
    {
        $roleId = $request->input('role_id');
        $permissionIds = $request->input('permission_ids');
        $role = Role::find($roleId);
        $role->perms()->sync($permissionIds);
        return $this->respondOk();
    }

    public function getRolePermissions($role_id)
    {
        $role = Role::find($role_id);
        $permissionIds = $role->perms->pluck('id');
        return $permissionIds;
    }

    public function addRoleToUser(Request $request)
    {
        $staffId = $request->input('staff_id');
        $roleIds = $request->input('role_ids');
        Staff::find($staffId)->user->roles()->sync($roleIds);
        return $this->respondOk();
    }

    public function searchInStaff(Request $request)
    {
        $email = $request->input('email');
        $staff = User::where('userable_type', 'App\Staff')->where('email', $email)->first();
        if (!is_null($staff)){
            return $staff->userable->toArray();
        }
        throw new ModelNotFoundException;
    }
}
