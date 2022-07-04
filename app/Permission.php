<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 2/19/17
 * Time: 5:44 PM
 */

namespace App;


use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{
    protected $fillable = ['name'];
    const BUY_FROM_BAZAAR = 'buy_from_bazaar';
    const BUY = 'buy';
    const CREATE_STORE = 'create_store';
    const MANIPULATE_PRODUCT = 'manipulate_product';
    const VERIFY_COMMENT = 'verify_comment';
    const VERIFY_PRODUCT = 'verify_product';
    const EDIT_USER = 'edit_user';
    const VERIFY_ORDER = 'verify_order';
    const SEE_ALL_BAZAAR_ORDERS = 'see_all_bazaar_orders';
    const MANIPULATE_SPECIFICATIONS = 'manipulate_specification';
    const ADD_COLOR = 'add_color';
    const MANIPULATE_ROLE_PERMISSIONS = 'manipulate_role_permissions';
    const MANIPULATE_ADDRESS = 'manipulate_address';
    const ACCESS_MESSAGE = 'access_message';
    const LIKE_COMMENT = 'like_comment';
    const MANIPULATE_BRAND = 'manipulate_brand';
    const ACCESS_BRAND = 'access_brand';
    const MANIPULATE_WEGO_MESSAGE = 'manipulate_wego_message';
    const SEE_WEGO_MESSAGE = 'see_wego_message';
    const EDIT_BUYER = 'edit_buyer';
    const GET_BUYER_PROFILE = 'get_buyer_profile';
    const ADD_PRODUCT_TO_WATCHLIST = 'add_product_to_watchlist';
    const HAS_FAVORITE = 'has_favorite';
    const ADD_REPORT = 'add_report';
    const VIEW_REPORTS = 'view_reports';
    const ADD_COMMENT = 'add_comment';
    const DELETE_COMMENT = 'delete_comment';
    const ADD_CRITICISM = 'add_criticism';
    const MANAGE_CRITICISM = 'manage_criticism';
    const ACCESS_AUDIT = 'access_audit';
    const PAY_AUDIT = 'pay_audit';
    const ADD_STORE_PICTURE = 'add_store_picture';
    const UPDATE_STORE_PICTURE = 'update_store_picture';
    const DELETE_TEMP_STORE_PICTURE = 'delete_temp_store_picture';
    const GET_STORE_PROFILE = 'get_store_profile';
    const EDIT_STORE = 'edit_store';
    const DELETE_PRODUCT = 'delete_product';
    const GET_SHIPPING_DETAIL = 'get_shipping_detail';
    const GENERATE_COUPON = 'generate_coupon';
    const MANIPULATE_BAZAAR = 'manipulate_bazaar';
    const ADD_HOLIDAY = 'add_holiday';
    const MANIPULATE_MENU = 'manipulate_menu';
    const DELETE_SITE_PICS = 'delete_site_pics' ;
}
