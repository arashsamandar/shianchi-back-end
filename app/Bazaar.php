<?php

namespace App;

use App\Http\Controllers\ApiController;
use Illuminate\Database\Eloquent\Model;
use DB;


class Bazaar extends Model
{
    protected $fillable = ['name', 'city_id', 'province_id', 'address'];

    public function staffs()
    {
        return $this->belongsToMany('App\Staff');
    }

    public function stores()
    {
        return $this->belongsToMany('App\Store');
    }

    public static function hasStaff($id)
    {
        if (DB::table('bazaar_staff')->where('bazaar_id', $id)->first() != null)
            (new ApiController())->respondWithError('بازار مورد نظر در حال استفاده می باشد');
    }

    public static function hasStore($id)
    {
        if (DB::table('bazaar_store')->where('bazaar_id', $id)->first() != null)
            (new ApiController())->respondWithError('بازار مورد نظر در حال استفاده می باشد');

    }

    public static function deleteStoresInsideBazaar($bazaar)
    {
        $stores = $bazaar->stores;

        $countAnyStoreInHowManyBazaar = Bazaar::getCountAnyStoreInHowManyBazaar();

        foreach ($stores as $store) {
            $storeId = $store->id;

            if (Bazaar::isStoreOnlyInOneBazaar($storeId,$countAnyStoreInHowManyBazaar)) {
                Store::findOrFail($storeId)->delete();
                Store::elastic()->addToIndex();
            }
        }
    }

    public static function getCountAnyStoreInHowManyBazaar()
    {
        $bazaarStoreTable = DB::table('bazaar_store')->get();

        $storeInBazaar = $bazaarStoreTable->groupBy('store_id')->map(function ($value) {
            return $value->count();
        });

        return $storeInBazaar;


//        $storeInBazaar = array();
//
//        foreach ($bazaarStoreTable as $bazaarStoreRow) {
//            array_push($storeInBazaar, $bazaarStoreRow->store_id);
//        }
//
//        return array_count_values($storeInBazaar);
    }

    public static function isStoreOnlyInOneBazaar($storeId,$countAnyStoreInHowManyBazaar)
    {

        if($countAnyStoreInHowManyBazaar[$storeId] == 1)
            return true;
        return false;
    }

}
