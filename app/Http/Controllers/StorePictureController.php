<?php

namespace App\Http\Controllers;

use Wego\PictureHandler;
use Wego\SavePicture;
use Illuminate\Http\Request;
use App\Http\Requests;
use Wego\UserHandle\UserPermission;
use Illuminate\Foundation\Validation\ValidatesRequests;

class StorePictureController extends ApiController
{
    protected $requestRules = [
        "x" => "required|numeric",
        "y" => "required|numeric",
        "width" => "required|numeric",
        "height" => "required|numeric",
        "type"=>'required|alpha'
    ];
    protected $pictureFunctionMap = [
        "cover"=>"coverPictureSave",
        "inside" => "insidePictureSave",
        "thumbnail"=>"thumbnailPictureSave"
    ];
    protected $typeValidationMap =[
        "coverPictureSave"=>"required|image|max:6000|dimensions:min_height=200,min_width=800",
        "insidePictureSave"=>"required|image|max:6000|dimensions:min_height=500,min_width=500",
        "thumbnailPictureSave"=>"required|image|max:6000|dimensions:min_height=130,min_width=130",
    ];
    protected $storePicture;
    const THUMBNAIL_RESIZE=130;
    const INSIDE_RESIZE=500;
    Const COVER_WIDTH=800;
    const COVER_HEIGHT=200;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\StoreTemporaryStorePictureRequest $request){
        $user = UserPermission::checkOrOfPermissions([UserPermission::STAFF,UserPermission::STORE]);

        $funcName = $this->pictureFunctionMap[$request->input('type')];

        $validationPictureName = $request->input('type').'_store_pic';
        $this->requestRules[$validationPictureName] = $this->typeValidationMap[$funcName];
        $path = $this->$funcName($request,$user['userable_id']);
        return $this->respondOk($path,'path');
    }

    private function coverPictureSave(Request $request,$userId){

        return (new PictureHandler($request,$userId))
            ->setResizeOption([])
            ->setUsePath("store")
            ->saveRectangle(self::COVER_WIDTH,self::COVER_HEIGHT);
    }
    private  function insidePictureSave(Request $request,$userId)
    {
        return (new PictureHandler($request,$userId))
            ->setResizeOption([self::INSIDE_RESIZE])
            ->setUsePath("store")
            ->save();
    }
    private function thumbnailPictureSave(Request $request, $userId)
    {
        return (new PictureHandler($request,$userId))
            ->setResizeOption([self::THUMBNAIL_RESIZE])
            ->setUsePath("store")
            ->save();
    }
}
