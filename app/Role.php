<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 2/19/17
 * Time: 5:45 PM
 */

namespace App;


use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    protected $fillable = ['name'];
    const LOCAL_STAFF = 'local_staff';
    const BAZAAR_STAFF = 'bazaar_staff';
    const SHIPPING_STAFF = 'shipping_staff';
    const BUYER = 'buyer';
    const STORE = 'store';
    const ADMINISTRATOR = 'administrator';
    public static $roles = [
        self::LOCAL_STAFF => [
            Permission::VERIFY_COMMENT,
            Permission::MANIPULATE_PRODUCT,
            Permission::VERIFY_PRODUCT,
            Permission::SEE_ALL_BAZAAR_ORDERS,
            Permission::MANIPULATE_SPECIFICATIONS,
            Permission::EDIT_USER,
            Permission::ADD_COLOR,
            Permission::MANIPULATE_ROLE_PERMISSIONS,
            Permission::MANIPULATE_BRAND,
            Permission::ACCESS_BRAND,
            Permission::MANIPULATE_WEGO_MESSAGE,
            Permission::SEE_WEGO_MESSAGE,
            Permission::GET_BUYER_PROFILE,
            Permission::ADD_PRODUCT_TO_WATCHLIST,
            Permission::DELETE_COMMENT,
            Permission::MANAGE_CRITICISM,
            Permission::PAY_AUDIT,
            Permission::CREATE_STORE,
            Permission::DELETE_PRODUCT,
            Permission::GET_SHIPPING_DETAIL
        ],
        self::BAZAAR_STAFF => [
            Permission::BUY_FROM_BAZAAR
        ],
        self::SHIPPING_STAFF => [

        ],
        self::BUYER => [
            Permission::BUY,
            Permission::MANIPULATE_ADDRESS,
            Permission::ACCESS_MESSAGE,
            Permission::LIKE_COMMENT,
            Permission::SEE_WEGO_MESSAGE,
            Permission::GET_BUYER_PROFILE,
            Permission::EDIT_BUYER,
            Permission::ADD_PRODUCT_TO_WATCHLIST,
            Permission::HAS_FAVORITE,
            Permission::ADD_COMMENT,
            Permission::DELETE_COMMENT,
            Permission::ADD_CRITICISM,
            Permission::GET_SHIPPING_DETAIL
        ],
        self::ADMINISTRATOR => [
            Permission::MANIPULATE_SPECIFICATIONS,
            Permission::MANIPULATE_WEGO_MESSAGE
        ],
        self::STORE => [
            Permission::MANIPULATE_PRODUCT,
            Permission::ACCESS_MESSAGE,
            Permission::ACCESS_BRAND,
            Permission::SEE_WEGO_MESSAGE,
            Permission::ACCESS_AUDIT,
            Permission::GET_STORE_PROFILE,
            Permission::EDIT_STORE,
            Permission::DELETE_PRODUCT,
            Permission::GET_SHIPPING_DETAIL
        ],
    ];

    public static function getRoleId($roleName)
    {
        $roleId = Role::where('name', '=', $roleName)->first()->id;
        return $roleId;
    }
}
