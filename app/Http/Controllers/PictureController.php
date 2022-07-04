<?php

namespace App\Http\Controllers;

use App\Menu;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Wego\PictureHandler;

class PictureController extends ApiController
{
    use Helpers;
    public function saveTempPicture(Request $request)
    {
        $path= (new PictureHandler())->setRequest($request)->setPath('wego/staff/staff/sitePics/temp')->save();
        return $this->respondOk($path,"path");
    }

    public function delete(Request $request)
    {
        $user = $this->auth->user();
        $path = $request['path'];
        if ($this->isTempPicture($path)){
            (new PictureHandler())->deleteTempPicture($path);
            return $this->respondOk();
        }
        (new PictureHandler())->deletePicturesFromFile($user->userable,$path);
        Menu::where('image_path',$path)->update(['image_path'=>null]);
        return $this->respondOk();
    }

    private function isTempPicture($path)
    {
        return (strpos($path, "temp") !== false);
    }
}
