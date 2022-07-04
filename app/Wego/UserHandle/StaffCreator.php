<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/11/16
 * Time: 5:32 PM
 */

namespace Wego\UserHandle;


use App\Role;
use App\Staff;
use App\User;

class StaffCreator implements PersonCreator
{

    public function create($request)
    {
        $user = new User;
        $user->email = $request['email'];
        $user->password = bcrypt($request['password']);

        $staff = Staff::create($request);
        $staff->user()->save($user);
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function update(array $request, $id)
    {
        // TODO: Implement update() method.
    }

    public function show($id)
    {
        // TODO: Implement show() method.
    }
}