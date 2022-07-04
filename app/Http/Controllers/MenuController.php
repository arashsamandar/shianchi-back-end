<?php

namespace App\Http\Controllers;

use App\Menu;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Wego\PictureHandler;

class MenuController extends ApiController
{
    use Helpers;
    public function saveTempPicture(Request $request)
    {
        $user = $this->auth->user();
        return (new PictureHandler($request,$user->id))->setPath('wego/staff/staff'.$user->userable_id.'/menu/temp')->save();
    }

    public function savePermanent($picture)
    {
        $user = $this->auth->user();
        return (new PictureHandler())->moveAllPictures($picture,$user->userable,'Staff');
    }

    public function store(Request $request)
    {
        $request['image_path'] = $this->savePermanent($request['image_path']);
        $menu = Menu::create($request->all());
        return $this->respondOk($menu->id,"id");
    }

    public function update(Request $request)
    {
        $items =$request->input('data');
        $insertItems = [];
        foreach ($items as $item) {
            if (array_key_exists('image_path',$item)) {
                if ($this->isPictureChanged($item)) {
                    $item['image_path'] = $this->savePermanent($item['image_path']);
                }
            } else {
                $item['image_path'] ='';
            }
            $insertItems[] = $item;
        }
        DB::transaction(function () use ($insertItems) {
            DB::table('menu')->delete();
            Menu::insert($insertItems);
        });
        return $this->respondOk('menu items updated');
    }

    private function isPictureChanged($request)
    {
        return $this->isTempPicture($request['image_path']);
    }

    public function deletePicture(Request $request)
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

    public function delete(Request $request)
    {
        $user = $this->auth->user();
        $menuIds = $request['ids'];
        $menuIds = is_array($menuIds) ? $menuIds :  (array)$menuIds;
        $menuItems = Menu::whereNotIn('id',$menuIds)->get();
        foreach ($menuItems as $menuItem){
            (new PictureHandler())->deletePicturesFromFile($user->userable,$menuItem->image_path);
        }
        Menu::whereNotIn('id',$menuIds)->delete();
        return $this->respondOk();
    }

    public function getAll()
    {

        return Menu::all()->toArray();
    }

}
