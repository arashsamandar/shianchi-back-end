<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 12/27/15
 * Time: 10:37 AM
 */

namespace Wego\UserHandle;

use App\Exceptions\NotBuyerException;
use App\Exceptions\NotPermittedException;
use App\Exceptions\NotStaffException;
use App\Exceptions\NotStoreException;
use App\Exceptions\PermissionNotAllowed;
use App\Permission;
use App\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserPermission
{
    const USERABLE_TYPE = 'userable_type';
    const BUYER = 'App\Buyer';
    const STAFF = 'App\Staff';
    const ADMIN = 'App\Admin';
    const STORE = 'App\Store';
    protected $user;

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function hasCreateShopAbility()
    {
        return $this->user[self::USERABLE_TYPE] === self::STAFF;
    }

    public function hasConfirmCommentAbility()
    {
        return $this->user[self::USERABLE_TYPE] === self::STAFF;
    }

    public function hasCreateStaffAbility()
    {
        return $this->user[self::USERABLE_TYPE] === self::ADMIN;
    }

    public function hasOrderAbility()
    {
        return $this->user[self::USERABLE_TYPE] === self::BUYER;
    }

    public function hasStoreAbility()
    {
        return $this->user[self::USERABLE_TYPE] == self::STORE;
    }

    public function hasBuyerAbility()
    {
        return $this->user[self::USERABLE_TYPE] == self::BUYER;
    }

    public function hasEditInfo($id)
    {
        return $this->user['userable_id'] == $id;
    }

    public function isStaff()
    {
        return $this->user[self::USERABLE_TYPE] === self::STAFF;
    }

    public function isStore()
    {
        return $this->user[self::USERABLE_TYPE] === self::STORE;
    }

    public function isBuyer()
    {
        return $this->user[self::USERABLE_TYPE] === self::BUYER;
    }

    public static function checkPermission($permissionNames)
    {
        $user = JWTAuth::parsetoken()->authenticate();
        $userPermissions = User::where('id', $user->id)->with(['roles' => function ($query) use ($permissionNames) {
            $query->with(['permissions' => function ($query) use ($permissionNames) {
                $query->whereIn('name', $permissionNames)->select(['permissions.name', 'permissions.id']);
            }])->select('roles.id');
        }])->select(['id'])->get()->toArray();
        if (collect(array_column(array_column($userPermissions, 'roles')[0], 'permissions'))->flatten(1)->has('0'))
            return $user;
        throw new AccessDeniedHttpException();
    }

    public static function checkStaffPermission()
    {
        $user = JWTAuth::parsetoken()->authenticate();
        $userPermission = new UserPermission();
        $userPermission->setUser($user);
        if ($userPermission->isStaff()) {
            return $user;
        }
        throw new AccessDeniedHttpException();
    }

    public static function checkStorePermission()
    {
        $user = JWTAuth::parsetoken()->authenticate();
        $userPermission = new UserPermission();
        $userPermission->setUser($user);
        if ($userPermission->isStore()) {
            return $user;
        }
        throw new AccessDeniedHttpException();
    }

    public static function checkBuyerPermission($userId = null)
    {
        $user = JWTAuth::parsetoken()->authenticate();
        $userPermission = new UserPermission();
        $userPermission->setUser($user);
        if ($userPermission->isBuyer()) {
            return $user;
        } elseif ($userPermission->checkPermission([Permission::EDIT_USER]))
            return User::findOrFail($userId);
        throw new AccessDeniedHttpException;
    }

    public static function checkOrOfPermissions($permissionTypes)
    {
        $user = JWTAuth::parsetoken()->authenticate();
        $userPermission = new UserPermission();
        $userPermission->setUser($user);
        foreach ($permissionTypes as $type) {
            switch ($type) {
                case self::BUYER:
                    if ($userPermission->isBuyer())
                        return $user;
                    break;

                case self::STAFF:
                    if ($userPermission->isStaff())
                        return $user;
                    break;

                case self::STORE:
                    if ($userPermission->isStore())
                        return $user;
                    break;

                default:
                    break;
            }
        }
        throw new AccessDeniedHttpException();
    }

    public static function getUserByCookie(Request $request)
    {
        if ($request->hasCookie('token')) {
            $user = JWTAuth::setToken($request->cookie()['token'])->authenticate();
            return $user;
        }
        return null;
    }

    public static function getUserFromTokenOrRequest($userId)
    {
        $user = JWTAuth::parsetoken()->authenticate();
        $userPermission = new UserPermission();
        $userPermission->setUser($user);
        if ($userPermission->isBuyer()) {
            return $user;
        } elseif ($userPermission->isStaff())
            return User::findOrFail($userId);
        throw new AccessDeniedHttpException;
    }

}