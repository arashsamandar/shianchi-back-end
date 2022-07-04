<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/25/16
 * Time: 5:24 PM
 */

namespace Wego\Store;


use App\Store;
use Illuminate\Http\Request;

class SaveManagerPicture
{

    public function save(Request $requests, Store $store)
    {
        $file = $requests->file('file');

        $name = time().$file->getClientOriginalName();

        $file->move('wego/photo',$name);
        // save to db  $store->photo()->create(['path'=>'wego/photos/{$name}]);
        return 'done';
    }
}