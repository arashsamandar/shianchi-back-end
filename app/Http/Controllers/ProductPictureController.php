<?php

namespace App\Http\Controllers;

use App\Product;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Validator;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\File;
use Wego\PictureHandler;
use Wego\SavePicture;
use Wego\UserHandle\UserPermission;

class ProductPictureController extends ApiController
{
    use Helpers;
    const PRODUCT_SIZE_OPTION1 = 390;
    const PRODUCT_SIZE_OPTION2 = 150;

    protected $requestRules = [
        "pic" => "required|image|max:6000",
        "x" => "required|numeric",
        "y" => "required|numeric",
        "width" => "required|numeric",
        "height" => "required|numeric",
    ];

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\StoreTemporaryProductPictureRequest $request)
    {
        $user = $this->auth->user();

        $validator = Validator::make($request->toArray(), $this->requestRules);
        if ($validator->fails())
            return $validator->errors();
        $saveProductPicture = new PictureHandler($request, $user['userable_id']);
        return $this->respondOk($saveProductPicture->setResizeOption([self::PRODUCT_SIZE_OPTION1, self::PRODUCT_SIZE_OPTION2])->setUsePath("product")->save(), 'path');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the srpecified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * Todo in tabe dg be dard nemikhore???
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = UserPermission::checkStorePermission();
        $saveProductPicture = new PictureHandler($request, $user['userable_id']);
        $path = $saveProductPicture->setResizeOption([ProductPictureController::PRODUCT_SIZE_OPTION1,
            ProductPictureController::PRODUCT_SIZE_OPTION2])->setUsePath("product")->save();
        return $this->respondOk($path, 'path');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
//    public function destroy(Request $request)
//    {
//        $store = UserPermission::checkStorePermission();
//
//        if(strpos($request->input('path'),'/store'.$store->userable_id.'/') === false)
//            return $this->setStatusCode(403)->respondWithError("access denied");
//
//        $prefixes = ['150','250','original'];
//        $path = public_path().$request->input('path');
//        $baseName = (basename($path));
//
//        $preBaseName = explode('_',$baseName);
//
//        foreach ($prefixes as $prefix) {
//            File::delete(str_replace($preBaseName[0],$prefix,$path));
//        }
//
//        return $this->respondOk('deleted completed');
//    }
}
