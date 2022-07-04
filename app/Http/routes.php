
<?php
/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use App\Permission;
use Elasticsearch\ClientBuilder;

$api = app('Dingo\Api\Routing\Router');
//if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
//    // Ignores notices and reports all other kinds... and warnings
//    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//    //     error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
//}
$api->version('v1', ['namespace' => 'App\Http\Controllers', 'middleware' => 'throttle'], function ($api) {

    $api->get('getCategoryJson', 'CategoryController@getCategoryForMenu');

    $api->get('getSiteMap', 'SitemapController@siteMap');
//    $api->get('getMenuJson','CategoryController@getCategoryForMenu');

    $api->post('saveCategories', 'CategoryController@saveCategories');

    $api->post('shipping', ['middleware' => ['api.auth', 'permission:' . Permission::GET_SHIPPING_DETAIL], 'uses' => 'ShippingController@store']);

    $api->post('checkTokenValidity','AuthenticateController@checkTokenValidity');


    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::MANIPULATE_ROLE_PERMISSIONS]], function ($api) {
        $api->get('role/getAll','RoleController@getAllRoles');
        $api->get('permission/getAll','RoleController@getAllPermissions');
        $api->post('role/store','RoleController@storeRole');
        $api->post('permission/store','RoleController@storePermission');
        $api->get('role/permissions/{role_id}','RoleController@getRolePermissions');
        $api->post('role/addPermission','RoleController@addPermissionsToRole');
        $api->post('role/addRoleToStaff','RoleController@addRoleToUser');
        $api->post('staff/search','RoleController@searchInStaff');

    });



    $api->post('coupon/store', ['middleware' => ['api.auth','permission:' . Permission::GENERATE_COUPON],
        'uses' => 'CouponController@storeCoupon']);
    $api->post('percentCoupon/store', ['middleware' => ['api.auth','permission:' . Permission::GENERATE_COUPON],
        'uses' => 'CouponController@storePercentCoupon']);
    $api->post('gift/store', ['middleware' => ['api.auth','permission:' . Permission::GENERATE_COUPON],
        'uses' => 'GiftController@store']);
    $api->get('coupon/{id}','CouponController@getCoupon');




    $api->post('storeExcelForAnyCategory','ExcelController@storeExcelForAnyCategory');




    //deleteAllSpecificationsByCategories
    $api->post('authenticate', 'AuthenticateController@store');
    //$api->resource('authenticate', 'AuthenticateController');
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::EDIT_BUYER]], function ($api) {
        $api->post('buyer/picture/change', 'BuyerController@changePicture');
        $api->post('buyer/update', 'BuyerController@Update');
        $api->post('buyer/picture/save', 'BuyerController@savePicture');
        $api->post('buyer/picture/delete', 'BuyerController@deletePicture');
        $api->post('buyer/picture', 'BuyerController@tempStorePicture');

    });
    $api->get('buyer/getJson', ['middleware' => ['api.auth', 'permission:' . Permission::GET_BUYER_PROFILE], 'uses' => 'BuyerController@getJson']);
    $api->get('buyer/coin_by_store', 'BuyerController@coinByStore');
    $api->get('buyer/coin_by_expiration', 'BuyerController@coinByExpiration');
    $api->post('buyer', 'BuyerController@store');
    //todo stalker user??
    $api->post('user/watchlist', ['middleware' => ['api.auth', 'permission:' . Permission::ADD_PRODUCT_TO_WATCHLIST], 'UserController@addToWatchlist']);

    $api->post('search', 'SearchController@index');

    $api->get('searchKeyWord', 'SearchController@keyword');

    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::HAS_FAVORITE]], function ($api) {
        $api->get('getStoreFavorite', 'FavoriteController@getStoreFavorite');
        $api->get('getProductFavorite', 'FavoriteController@getProductFavorite');
        $api->post('addProductToFavorite', 'FavoriteController@addProductToFavorites');
        $api->post('addStoreToFavorite', 'FavoriteController@addStoreToFavorites');
        $api->post('favorite/delete/{id}', 'FavoriteController@destroy');
    });


    $api->get('province', 'ProvinceController@store');
    $api->post('province', 'ProvinceController@store');


    $api->resource('score', 'ProductScoreController');
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::ADD_REPORT]], function ($api) {
        $api->post('/storeStoreReport', 'ReportController@storeStoreReport');
        $api->post('/storeProductReport', 'ReportController@storeProductReport');

    });
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::VIEW_REPORTS]], function ($api) {
        $api->get('report', 'ReportController@index');
        $api->get('report/{id}', 'ReportController@show');
        $api->get('/report/storeReports', 'ReportController@getStoreReports');
        $api->get('/report/productReports', 'ReportController@getProductReports');
        $api->get('/report/byType', 'ReportController@getReportByType');
        $api->post('report/read/{id}', 'ReportController@reportRead');
        $api->post('report/{id}', 'ReportController@destroy');
        $api->get('/getReportedProduct/{id}', 'ReportController@getReportedProduct');
        $api->get('/getReportedStore/{id}', 'ReportController@getReportedStore');

    });
    $api->get('/report/getTypes', 'ReportController@getTypes');

    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::VERIFY_COMMENT]], function ($api) {
        $api->get('comment/getInProgressComments', 'CommentController@getInProgressComments');
        $api->post('comment/confirm/{id}', 'CommentController@confirm');
        $api->get('comment/reject/{id}', 'CommentController@rejectComment');
    });
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::ADD_COMMENT]], function ($api) {
        $api->post('comment', 'CommentController@store');
        $api->get('comment/getUserComments', 'CommentController@getUserComments');
    });
    $api->get('comment/getCommentsByRating', 'CommentController@getCommentsByRating');
    $api->get('comment/getCommentsByTime', 'CommentController@getCommentsByTime');
    $api->post('comment/delete/{id}', ['middleware' => ['api.auth', 'permission:' . Permission::DELETE_COMMENT], 'uses' => 'CommentController@destroy']);



    // $api->resource('like','LikeController');

//    $api->post('message','MessageController@store');
//    $api->get('message','MessageController@index');
//    $api->post('message/delete/{id}','MessageController@destroy');
//    $api->post('message/read','MessageController@readMessage');
//    $api->post('messageByUserId','MessageController@sendMessageToUser');
    $api->group(['prefix' => 'message', 'middleware' => ['api.auth', 'permission:' . Permission::ACCESS_MESSAGE]], function ($api) {
        $api->post('/addReply/{id}/', 'MessageController@addReplyToMessage');
        $api->post('/deleteMessage/{id}', 'MessageController@destroy');
        $api->get('/getSenderUnreadMessages', 'MessageController@getSenderUnreadMessages');
        $api->get('/getSenderMessages', 'MessageController@getSenderMessages');
        $api->get('/getReceiverUnreadMessages', 'MessageController@getReceiverUnreadMessages');
        $api->get('/getReceiverMessages', 'MessageController@getReceiverMessages');
        $api->post('/readMessage/{id}', 'MessageController@readMessage');
        $api->post('/', 'MessageController@store');
    });

    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::SEE_WEGO_MESSAGE]], function ($api) {
        $api->post('message/readWegoMessage/{id}', 'WegoMessageController@readWegoMessage');
        $api->get('message/getUnreadWegoMessages', 'WegoMessageController@getUnreadWegoMessages');
        $api->get('message/getWegoMessages', 'WegoMessageController@getWegoMessages');
    });
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::MANIPULATE_WEGO_MESSAGE]], function ($api) {
        $api->post('message/addWegoMessage', 'WegoMessageController@store');
        $api->post('message/deleteWegoMessage/{id}', 'WegoMessageController@destroy');
    });

    $api->post('staff', 'StaffController@store');
    //todo : dakhele cart controller tabe he be ezaye staff ye user bar migardune be ezaye buyer yechi dg ! ino un moghe ke khasti repository barash besazi handle kon
    $api->post('cart', 'CartController@index');

    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::VERIFY_PRODUCT]], function ($api) {
        $api->post('order/update/{id}', 'OrderController@update');
        $api->post('order/cancel/{id}', 'OrderController@cancelOrder');
        $api->delete('order/{id}','OrderController@delete');
        $api->post('setOrderStatus/{id}','OrderController@orderStatus');
        $api->post('setOrderProductStatus','OrderController@orderProductStatus');
    });
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::SEE_ALL_BAZAAR_ORDERS]], function ($api) {
        $api->get('order/{id}', 'OrderController@show');
        $api->get('order', 'OrderController@index');
        $api->get('orderProducts','OrderController@orderProduct');
        $api->get('searchOrderProducts','OrderController@searchOrderProduct');
        $api->get('searchOrders','OrderController@searchOrders');
    });
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::BUY_FROM_BAZAAR]], function ($api) {
        $api->post('order/getBazaarInProgressProductsByStoreFromInProgressOrders', 'OrderController@getBazaarInProgressProductsByStoreFromInProgressOrders');
        $api->post('order/getBazaarAvailableOrdersByStore', 'OrderController@getBazaarAvailableOrdersByStore');
        $api->post('order/getBazaarAvailableProductsByStoreFromAvailableOrders', 'OrderController@getBazaarAvailableProductsByStoreFromAvailableOrders');
        $api->post('order/setOrderProductAvailable', 'OrderController@setOrderProductStatusToAvailable');
        $api->post('order/setOrderProductUnavailable', 'OrderController@setOrderProductStatusToUnavailable');
        $api->post('order/setOrderProductPurchased', 'OrderController@setOrderProductStatusToPurchased');
    });
    $api->post('order/delivered/{id}', 'OrderController@setOrderToDelivered');

    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::BUY]], function ($api) {
        $api->post('order', 'OrderController@store');
        $api->post('checkOrderPrices','OrderController@checkOrderPrices');
        $api->get('buyerOrder', 'OrderController@buyerOrders');
    });

   // $api->get('storeOrder', 'OrderController@getStoreOrders');
//

    $api->post('compare', 'CompareController@show');
    $api->post('compareList', 'CompareController@find');

    $api->get('store/audits', ['middleware' => ['api.auth', 'permission:' . Permission::ACCESS_AUDIT], 'uses' => 'AuditController@getStoreAudits']);
    $api->post('payAudit', ['middleware' => ['api.auth', 'permission:' . Permission::PAY_AUDIT], 'uses' => 'AuditController@payAudit']);


    $api->get('store/categories', 'StoreController@getCategories');

    $api->post('store/picture/delete', 'StoreController@deletePicture');
    $api->resource('store/picture', 'StorePictureController');
    $api->get('store/getProfileJson', ['middleware' => ['api.auth', 'permission:' . Permission::GET_STORE_PROFILE], 'uses' => 'StoreController@getProfileJson']);
    $api->get('store/getSearchSummaryJson', 'StoreController@getSearchSummaryJson');
    $api->post('store/getPageJson', 'StoreController@getPageJson');
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::EDIT_STORE]], function ($api) {
        $api->post('store/delete/{id}','StoreController@deleteStore');
        $api->post('store/updatePassword', 'StoreController@updatePassword');
        $api->post('store/updateWorkTime', 'StoreController@updateWorkTime');
        $api->post('store/updateGuarantee', 'StoreController@updateGuarantee');
        $api->post('store/updatePictures', 'StoreController@updatePictures');
        $api->post('store/department/deletePicture', 'DepartmentController@deleteDepartmentManagerPicture');
        $api->post('store/deleteManagerPicture', 'StoreController@deleteManagerPicture');
        $api->post('store/updateDepartments', 'StoreController@updateDepartments');
        $api->post('store/updateDescription', 'StoreController@updateDescription');
        $api->post('store/updateWegoCoinExpiration', 'StoreController@updateWegoCoinExpiration');
        $api->post('store/update', 'StoreController@update');
        $api->post('store/addTelegramUsername','StoreController@storeTelegramUsername');
        $api->post('store/orders', 'OrderController@storeOrder');
        $api->get('product/getDetails/{id}','ProductDetailController@getProductDetails');
    });
    $api->post('store', ['middleware' => ['api.auth', 'permission:' . Permission::CREATE_STORE], 'uses' => 'StoreController@store']);
    $api->post('store/search', ['middleware' => ['api.auth', 'permission:' . Permission::CREATE_STORE], 'uses' => 'StoreController@searchStore']);


    $api->get('/invalidToken','TokenController@invalidThisToken');


    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::VERIFY_PRODUCT]], function ($api) {
        $api->get('product/pre_confirmed', 'ProductController@getPreConfirmedProducts');
        $api->get('product/not_confirmed', 'ProductController@getNotConfirmedProducts');
        $api->post('product/set_to_confirmed/{id}', 'ProductController@setToConfirmed');
        $api->post('product/set_to_not_confirmed/{id}', 'ProductController@setToNotConfirmed');
        $api->post('warranty','WarrantyController@store');
        $api->post('warranty/update','WarrantyController@update');
        $api->post('warranty/delete/{id}','WarrantyController@delete');
    });
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::MANIPULATE_PRODUCT]], function ($api) {
        $api->get('product/not_confirmed_by_store', 'ProductController@getNotConfirmedProductsByStore');
        $api->get('product/pre_confirmed_by_store', 'ProductController@getPreConfirmedProductsByStore');
        $api->get('getProductsByCategory', 'ProductController@getProductsByCategory');
        $api->post('product/picture/delete', 'ProductController@deletePicture');
        $api->post('product/updatePrice/{id}','ProductController@updatePrice');
        $api->post('product/updateQuantity/{id}','ProductController@updateQuantity');
        $api->post('product/picture', 'ProductPictureController@store');
        $api->get('product/watchlist', 'ProductController@getWatchlistCount');
        $api->post('product/update/{id}', 'ProductController@update');
        $api->post('product', 'ProductController@store');
        $api->get('lastProducts','ProductController@lastProductsByStore');
        $api->get('lastProductGroups','ProductController@lastProductGroupsByStore');
        $api->get('sendProductToTelegramChannel','ProductController@sendProductMessageToStoreTelegram');
    });
    $api->get('product/getJson/{id}', 'ProductController@getJson');
    $api->get('getProductGroup', 'ProductController@getProductGroup');
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::DELETE_PRODUCT]], function ($api) {
        $api->post('deleteConfirmedProduct/{id}', 'ProductController@deleteConfirmedProduct');
        $api->post('deleteNotConfirmedProduct/{id}', 'ProductController@deleteNotConfirmedProduct');
    });
    $api->get('product/{id}', 'ProductController@getProductPageJson');
    $api->get('product/{letter}','ProductController@getProductByPersianName'); // Arash Code .
    $api->get('productAmp/{id}', 'ProductController@getProductForAmp');
    $api->post('productWatched','ProductController@productWatched');
    $api->post('product/importFromExcel', 'ExcelController@importProductFromExcel');
    $api->post('storeExcel', 'ExcelController@storeExcel');
    $api->get('searchProduct','ProductController@search');
    $api->get('store/productSearch','ProductController@searchStoreProducts');
    $api->get('getAllProductDetails/{id}','ProductController@getAllProductDetails');

    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::MANIPULATE_BRAND]], function ($api) {
        $api->post('brand', 'BrandController@saveBrand');
        $api->post('brand/setRelationWithCategory', 'BrandController@setCategoryBrandRelation');
        $api->post('brand/update', 'BrandController@updateBrand');
        $api->post('brand/delete/{id}', 'BrandController@deleteBrand');
    });

    $api->get('brand/getAll', 'BrandController@getAllBrands');
    $api->get('brand/getBrandByCategory/{categoryId}', 'BrandController@getBrandsByCategoryId');
    $api->get('brand/search', 'BrandController@searchBrand');


    $api->get('getCategoriesByPersianName', 'CategoryController@getLeafCategoryPersianName');
    $api->get('getCategorySpecificationsAndValuesByName', 'CategoryController@getCategorySpecificationsAndValuesByName');
    $api->get('getCategorySpecificationsAndValuesById/{id}', 'CategoryController@getCategorySpecificationsAndValuesById');

    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::ADD_COLOR]], function ($api) {
        $api->post('/color/save', 'ColorController@store');
        $api->post('/color/update', 'ColorController@update');
        $api->post('/color/delete/{id}', 'ColorController@delete');
    });
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::MANIPULATE_BAZAAR]], function ($api) {
        $api->post('/bazaar/save','BazaarController@store');
        $api->post('/bazaar/update','BazaarController@update');
        $api->post('/bazaar/delete/{id}','BazaarController@delete');
    });
    $api->get('/bazaar','BazaarController@index');
    $api->get('/bazaar/search','BazaarController@search');


    $api->get('similarCategory', 'CategoryController@similarCategory');
    $api->get('getChildCategoryWithBreadCrumb', 'CategoryController@getChildCategoryWithBreadCrumb');
    $api->get('getRootCategories', 'CategoryController@getRootCategories');
    $api->group(['prefix' => 'address', 'middleware' => ['api.auth', 'permission:' . Permission::MANIPULATE_ADDRESS]], function ($api) {
        $api->post('/update/{id}', 'AddressController@update');
        $api->post('/delete/{id}', 'AddressController@destroy');
        $api->post('/', 'AddressController@store');
        $api->get('/', 'AddressController@getBuyerAddresses');
        $api->get('getPredictorInfo','WorldcupController@getUserPredictionInfo');
        $api->post('predict','WorldcupController@predict');
    });
    $api->group(['prefix' => 'address', 'middleware' => ['api.auth', 'permission:' . Permission::EDIT_USER]], function ($api) {
        $api->post('/updateAsAdmin/{id}', 'AddressController@updateAsAdmin');
        $api->post('/deleteAsAdmin/{id}', 'AddressController@destroyAsAdmin');
        $api->post('/asAdmin', 'AddressController@storeAsAdmin');
        $api->get('/asAdmin' , 'AddressController@getBuyerAddressesAsAdmin');
    });


    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::MANIPULATE_SPECIFICATIONS]], function ($api) {
        $api->post('values', 'SpecificationController@saveValues');
        $api->post('specification', 'SpecificationController@saveSpecification');
        $api->post('specification/deleteAllCategorySpecifications', 'SpecificationController@deleteAllSpecificationsByCategories');
        $api->post('specificationTitle/deleteAllTitlesByCategory', 'SpecificationController@deleteAllSpecificationTitlesByCategories');
        $api->post('specification/update/', 'SpecificationController@updateSpecification');
        $api->post('values/update/', 'SpecificationController@updateValues');
        $api->post('specificationTitle/update/', 'SpecificationController@updateTitles');
        $api->post('specification/delete/{id}', 'SpecificationController@deleteSpecification');
        $api->post('specificationTitle', 'SpecificationController@saveTitle');
        $api->post('specificationTitle/delete/{id}', 'SpecificationController@deleteTitle');
    });

    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::MANIPULATE_SPECIFICATIONS]], function ($api) {
        $api->post('/menu','MenuController@store');
        $api->post('/menu/update','MenuController@update');
        $api->post('/menu/delete','MenuController@delete');
        $api->post('/menu/saveTempPicture','MenuController@saveTempPicture');
        $api->post('/menu/deletePicture','MenuController@deletePicture');
    });
    $api->get('/getMenuJson','MenuController@getAll');
    $api->post('/courseOrder','CourseController@handle');

    $api->post('/admin/picture','PictureController@saveTempPicture');
    $api->post('/admin/picture/delete', ['middleware' => ['api.auth','permission:' . Permission::DELETE_SITE_PICS],
        'uses' => 'PictureController@delete']);

    $api->post('password/forget', 'PasswordController@sendMailOfResetPassword');
    $api->post('password/reset', 'PasswordController@resetPasswordRequest');
    $api->post('specificationTitle/getAllCategoryTitles', 'SpecificationController@getSpecificationTitlesByCategory');
    $api->get('specification/{categoryId}', 'SpecificationController@getSpecificationsByCategory');
    //deleteAllSpecificationsByCategories
    $api->get('values/bySpecification/{id}', 'SpecificationController@getValuesBySpecification');


//    $api->get('needy', 'NeedyController@index');
//    $api->post('needy', 'NeedyController@store');
//    $api->post('needy/delete/{id}', 'NeedyController@destroy'); //todo in chie?? permission ham nazadam
//    $api->post('needy/setToHelped/{id}', 'NeedyController@setToHelped'); //todo in ham chie
//     $api->post('authenticate', 'AuthenticateController@authenticate');

    $api->get('department', 'DepartmentController@index');

    $api->get('/color', 'ColorController@index');

    $api->get('/country', 'CountryController@index');

    $api->get('/province/bazaars', 'ProvinceController@getBazaars');
    $api->post('/province/bazaars', 'ProvinceController@getBazaars');


    $api->post('/courseOrder', 'CourseController@handle');

    $api->post('order-new-product','OutsideOrderController@storeOrder');

//    $api->post('storeGameScore','GameScoreController@storeGameScore');

    $api->get('/google/auth','GoogleAuthController@login');

    $api->get('getTopRanks','GameScoreController@getRanking');

    $api->get('/addAllBazaarsToStaff','BazaarController@addAllBazaarsToLocalStaff');

    $api->post('setHoliday' ,['middleware' => ['api.auth', 'permission:' . Permission::ADD_HOLIDAY], 'uses' => 'OrderController@setHoliday']);
    $api->post('productOwnerValidation' ,['middleware' => ['api.auth', 'permission:' . Permission::GET_STORE_PROFILE], 'uses' => 'StoreController@checkStoreEditingValidation']);

    $api->get('/deleteEmptyPath/{productId}','ProductController@deleteEmptyPathProductPicture');
    $api->get('/addDiscountToPanasonic','ProductController@addDiscountToPanasonicPhones');
    $api->get('/changeCouponExpiration','CouponController@changeCouponExpiration');
    $api->get('/checkBuyPermission','UserController@checkBuyerPermission');
    $api->get('/finishOperation/{id}','OrderController@finishOperation');
    $api->get('/finishGameOperation/{id}','GameOrderController@finishGameOperation');
    $api->get('/getUserByOrderId/{id}','UserController@getUserByOderId');
    $api->get('/getMobileByOrderId/{id}','UserController@getMobileByOrderId');
    $api->get('/getOrderById/{id}','OrderController@getOrderById');
    $api->get('/getGameOrderById/{id}','GameOrderController@getGameOrderById');
    $api->get('/getClientId','ProductController@getClientId');
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::MANIPULATE_PRODUCT]], function ($api) {
        $api->get('/moveStoreProduct','ProductController@changeStoreOfProduct');
        $api->post('/changeProductStore','ProductController@changeProductStore');
        $api->post('/changeProductStoreWithoutConfirming','ProductController@changeProductStoreWithoutConfirming');
        $api->post('/changeProductBrand','ProductController@changeProductBrand');
        $api->get('/deleteBrandFromCategory','ProductController@deleteBrandFromCategory');
        $api->post('/setNotExistingProducts','ExcelController@setNotExistingProducts');
        $api->post('/checkPriceBaseExistStatus','ExcelController@checkPriceBaseExistStatus');
        $api->post('/checkPrice','ExcelController@checkPrice');
        $api->post('/uidByExcel','ExcelController@setUids');
        $api->post('/setToNotExistByCategoryBrand','ProductController@setToNotExistByCategory');
        $api->post('/setAllToNotExist','ProductController@setAllToNotExist');
        $api->post('/setToNotUpdateByCategoryBrand','ProductController@setToNotUpdateByCategory');
        $api->get('/setToNotExistByStore','ProductController@setToNotExistByStore');
        $api->get('addStoreTelegramChannel','StoreController@addStoreTelegramChannel');
        $api->post('copyProductsToAnotherStore','StoreController@copyProductsToAnotherStore');
        $api->post('pictureReplicating','StoreController@pictureReplicating');
        $api->get('/deleteDuplicateCategory','CategoryController@deleteDuplicateCategory');
        $api->get('changeStoreProductsPrice','StoreController@changeStoreProductsPrice');
        $api->get('changeStoreProductsPriceByBrandAndCat','StoreController@changeStoreProductsPriceByBrandAndCat');
        $api->get('changeProductsPriceByCatName','StoreController@changeStoreProductsPriceByCatName');
        $api->get('changeStoreProductsPriceByCat','StoreController@changeStoreProductsPriceByCategory');
        $api->get('setProductPrice','ProductController@setProductPrice');
        $api->post('generateNumberOfCoupons','CouponController@generateNumberOfCoupons');
        $api->post('/jashnvare','ProductController@setJashnvareKeyName');
        $api->post('/removeJashnvare','ProductController@removeJashnvareKeyName');
        $api->post('/removeJashnvareById','ProductController@removeJashnvareKeyNameById');
        $api->post('correctWeight','ExcelController@correctWeight');
        $api->get('/groupSmsToBuyers','BuyerController@sendGroupSmsToBuyers');
        $api->get('/groupSmsToGamers','BuyerController@sendGroupSmsToGameParticipants');
        $api->get('/groupEmailToBuyers','BuyerController@sendGroupEmailToBuyers');
        $api->get('/specificSmsAndEmail','BuyerController@sendSpecificBuyerSmsAndEmail');
        $api->get('/specificEmail','BuyerController@sendSpecificEmail');
        $api->get('/addCategoryToStore','StoreController@addCategoryToStore');
        $api->get('/changeBookPrice','ProductController@changeBooksPrice');
        $api->post('productDetails','StoreProductController@store');
        $api->post('productDetails/update','StoreProductController@update');
        $api->post('productDetails/delete/{id}','StoreProductController@delete');
        $api->get('/getShippingByAddress','AddressController@getShippingByAddress');
        $api->post('/setUid','ProductDetailController@setUid');
        $api->post('autoSetUid','ProductController@setUid');
        $api->get('/restoreAddress/{id}','OrderController@restoreAddress');
        $api->post('worldcupGame','WorldcupController@storeGame');
        $api->post('updateWorldcupGame','WorldcupController@updateGameTime');
        $api->post('setResult/{id}','WorldcupController@setResult');
        $api->post('setDailyOfferStatus/{id}','ProductController@setDailyOfferStatus');
        $api->post('saveBannerPicture','BannerController@saveTempPicture');
        $api->post('banner','BannerController@store');
        $api->post('gameGift/store','GameOrderController@storeGameGift');
        $api->get('getOrdersForApp','OrderController@getOrdersForApp');
        $api->get('getOfferDetailForApp/{id}','OrderController@getOfferDetailForApp');
        $api->post('submitOffer','OrderController@submitOffer');
        $api->get('getStoreCategoryBrand','StoreController@getStoreCategoryBrand');
        $api->post('addCategoryBrandToStore','StoreController@addCategoriesToStore');
    });
    $api->group(['middleware' => ['api.auth', 'permission:' . Permission::BUY]], function ($api) {
        $api->get('getPredictorInfo','WorldcupController@getUserPredictionInfo');
        $api->post('predict','WorldcupController@predict');
    });
    $api->get('updateMahestan','ProductController@updateMahestan');
    $api->get('correctExistStatus','ProductController@correctProductExistStatus');
    $api->get('deleteOldCategoryValues','ProductController@deleteOldProductValues');
    $api->get('sendAllProductsToRecommender','ProductController@sendAllProductsToRecommender');
    $api->get('setPropertyValues','ProductController@setProductsPropertyValue');
    $api->post('contactUs','CommentController@contactUs');
    $api->get('productDetails','ProductDetailController@show');
    $api->get('warranties','WarrantyController@index');
    $api->get('/deleteDuplicateProducts','ProductController@deleteDuplicateProducts');
    $api->get('/extendCoupon','CouponController@extendExpirationTime');
    $api->get('/getSimilarProducts/{id}','ProductController@getSimilarProducts');
    $api->get('/deliveryTime','ProductController@deliveryTime');
    $api->get('/nameCorrection','ProductController@nameCorrection');
    $api->get('/setOldOrdersStatus','OrderController@setOldOrdersStatus');
    $api->post('/setWarrantyAndName','ExcelController@setWarrantyAndEnglishName');
    $api->get('changeQuantities','ProductController@changeQuantities');
    $api->get('addGiftsToSamsungProducts','ProductController@addGiftsToSamsungProducts');
    $api->get('addGiftsToLGProducts','ProductController@addGiftsToLGProducts');
    $api->get('festivalProducts','ProductController@festivalProductByCategory');
    $api->get('OrderSms/{id}','OrderController@getOrderSmsToMyNumber');
    $api->get('getResults','WorldcupController@getGames');
    $api->get('getBestScores','WorldcupController@getBestScores');
    $api->get('getBestScoresForPrizes','WorldcupController@getBestScoresForPrizes');
    $api->get('pay/{id}','OrderController@pay');
    $api->get('banners','BannerController@index');
    $api->post('gameOrder','GameOrderController@store');
    $api->get('gameOrders','GameOrderController@index');
    $api->get('checkGameCoupon/{id}','GameOrderController@checkGameCoupon');
    $api->get('gameCounter','GameOrderController@getCounter');
    $api->get('getUserToken/{id}','OrderController@getUserToken');
    $api->post('submitOrderForNonExistingProducts','OutsideOrderController@submitOrderForNonExistingProducts');
    $api->get('changeSpecifications','SpecificationController@changeSpecifications');
    $api->post('testNotif','StoreController@sendNotifTest');
    $api->get('checkOldNotExistingOrders','ProductController@checkOldNotExistingOrders');
    $api->get('payUrl/{id}','OrderController@paymentUrl');
    $api->get('OrderSms/{id}','OrderController@getOrderSmsToMyNumber');
    //_____________________________Testing APIs_________________________
    $api->get('/sendmyemail','TestController@sendEmail');
    //__________________________________________________________________
    $api->post('/url','URLShortener@sendWithCurl');// useless , delete this and its code
    $api->get('/u','URLShortener@saveTheAddress');
    //________________________Creating SiteMap Manually_________________

    // Creating Product SiteMap
    $api->get('/createproductsitemap','SiteMapCreator@build');

    //_______________________End Of Manuall SiteMapp Apis_______________
});
Route::get('temp',function(){
    $params = [
        'index' => 'wego_1',
        'type' => 'products',
        'body' => [
            'query' => [
                'match_all' => []
            ]
        ]
    ];
    $client = ClientBuilder::create()->build();
    $results = $client->search($params);
    dd($results);
//    $products = \App\Product::where('brand_id',101)->get()->pluck('id')->toArray();
//    $detailIds = \App\ProductDetail::whereIn('product_id',$products)->get()->pluck('id')->toArray();
//    $orderIds = \App\OrderProduct::whereIn('detail_id',$detailIds)->where('created_at','>=',\Carbon\Carbon::now()->subMonth(1))
//        ->orderBy('order_id','desc')->get()->pluck('order_id')->toArray();
//    dd($orderIds);
//    $order = \App\Order::where('created_at','>=',\Carbon\Carbon::now()->subMonths(3))->get()->count();
//    $orderCounts = (int) ($order / 2);
//    $orders = \App\Order::where('created_at','>=',\Carbon\Carbon::now()->subMonths(3))->get();
//    $erfanianCount = 0;
//    $karamiCount = 0 ;
//    foreach ($orders as $order) {
//        if ($order->shipping_company == 'WegoJet'){
//            $count = \App\Order::where('id','<=',$order->id)
//                ->where('shipping_company','WegoJet')->count();
//        } else {
//            $count = \App\Order::where('id','<=',$order->id)
//                ->where('shipping_company','<>','WegoJet')->count();
//        }
//        $addition = '0';
//        if($count % 2 == 1){
//            $addition = '1';
//        }
//        if ($order->status == \App\Order::CANCELLED){
//            if ($addition == '0'){
//                $erfanianCount++ ;
//            } else {
//                $karamiCount++;
//            }
//        }
//    }
//    dd($orderCounts , "cancelled code 0 : ". $erfanianCount, "cancelled Code 1 : ".$karamiCount);

//    $keyName = request()->key_name ;
//    $ids = \App\ProductDetail::where('store_id',119)->get()->pluck('product_id')->toArray();
//    $products = \App\Product::whereIn('id',$ids)->get();
//    foreach ($products as $product) {
//        $product->key_name = $product->key_name . ' '.$keyName ;
//        $product->save();
//        \App\Product::where('id',$product->id)->elastic()->addToIndex();
//    }
//    $ids = \App\ProductDetail::where('store_id',107)->get()->pluck('product_id')->toArray();
//    $products = \App\Product::whereIn('id',$ids)->get();
//    foreach ($products as $product) {
//        \App\Product::where('id',$product->id)->elastic()->addToIndex();
//    }
//    return $this->respondOk();
//    \App\Category::elastic()->addToIndex();

//    $count = OutsideOrder::count();
//    $slackNotifier = new \Wego\Services\Notification\SlackNotifier();
//    $slackNotifier
//        ->setMessage("سفارش از کالای ناموجود-")
//        ->setReceiver('@sinapechaz1993')
//        ->send();
//    if(request()->id == 1) {
//        $prodids = \App\Product::where('category_id', 478)->pluck('id')->toArray();
//        $details = \App\ProductDetail::whereIn('product_id', $prodids)->orderBy('uid', 'desc')->pluck('uid')->toArray();
//        return $details;
//    }
//    set_time_limit(6000);
//    \Wego\Crawler::crawlNewCategory(['door-open']);
});
Route::get('setBookUids','ProductController@setBooksUids');
Route::get('trCoupon','CouponController@setAllOldCouponsType');
//Route::get('temp',function(){
//    $order = Order::whereNotNull('address_id')->first();
//    dd($order->address);
//    dd(\Wego\Helpers\PersianUtil::to_persian_num($order->address->prefix_mobile_number . $order->address->mobile_number . ' - ' .
//        $order->address->prefix_phone_number . $order->address->mobile_number));
//});
//Route::get('temp1','ProductController@transformProductsToNewStyle');
//Route::get('temp2','ProductController@addToElastic');
//Route::get('temp3','OrderController@transformOrdersToNewStyle');

Route::get('findwegoids','ExcelController@findWegobazaarId2');
Route::get('backFromGoogle','GoogleAuthController@login');
// Set webhook
Route::post('setWebhook','TelegramController@setTelegramWebhook');
// Example of POST Route:
Route::post('/<token>/webhook/', 'TelegramController@index');

Route::get('/waitingCourseOrder', 'CourseController@bankTransactionProcessForCourse');
Route::post('/check_transaction/{bankType}/{serviceType}/{element_id}', 'TransactionController@checkTransaction');
Route::get('/waitingOrder', 'OrderController@bankTransactionProcess');

//todo : __________________________________ This Section is Merely Test ________________________________________

//Route::get('/sendingsms','TestController@sendingSMS');
//Route::get('/arashsamandar','TestController@getPhpInfo');
//Route::get('/getItemByName/{name}','TestController@getItemByName');
//Route::get('/getItemById/{id}','TestController@getItemById');