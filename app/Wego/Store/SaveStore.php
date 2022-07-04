<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/21/16
 * Time: 9:59 AM
 */

namespace Wego\Store;

use App\Store;
use Wego\PictureHandler;

class SaveStore
{
    protected $user;
    public function save(array $requests)
    {
        $store = Store::create($requests);
        $this->user = $store->user()->create(
            [
                            'email' => $requests['email'] ,
                            'password' => bcrypt($requests['password']),
                            'name' => $requests['persian_name']
            ]);
        $store->manager_picture = $this->saveManagerPicture($requests['manager_picture'],$store);

        //$store->manager_picture = (preg_replace('/(.wego.staff.staff([0-9]+).temp.)(.*)/','/wego/store/store'.$store->id.'/storePicture'.'/'.'$3',$requests['manager_picture']));
        $store->url = strtolower(str_replace(' ','-',$store->english_name));
        $store->save();
        $store->bazaar()->attach($requests['bazaar']);
        return $store;
    }
    private function saveManagerPicture($tempPicPath,$store){
        return ((new PictureHandler())->moveAllPictures($tempPicPath,$store,"Store"));
    }
    public function getUser()
    {
        return $this->user;
    }

}