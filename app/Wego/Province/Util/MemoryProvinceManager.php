<?php

/**
 * Created by PhpStorm.
 * User: wb-2
 * Date: 6/1/16
 * Time: 10:17 AM
 */
namespace Wego\Province\Util;

use Illuminate\Support\Facades\Storage;
use Wego\Province\Province;

class MemoryProvinceManager extends ProvinceManager
{

    const TEHRAN_PROVINCE_ID = 1;
    const TEHRAN_CITY_ID = 1698;
    const REY_CITY_ID = 1707;
    /**
     * @param $id
     * @return mixed
     *
     * ostane id marboote ro barmigardoone
     */
    public function getProvince($id){
        $provinces = json_decode(Storage::get('provinces.json'),true)['provinces'];
        foreach($provinces as $province){
            if($province['id'] == $id){
                return (new Province())
                    ->setName($province['name'])
                    ->setCities($province['cities'])
                    ->setId($province['id']);
            }
        }
    }
    public function getCity(Province $province, $cityId){
        foreach($province->getCities() as $city){
            if($city['id'] == $cityId)
                return $city;
        }
        return null;
    }

    public function betterGetCity(Province $province, $cityId){
        array_search($cityId,$province->getCities(),array_column($province->getCities(),'id'));
    }

    public function getProvinceAndCity($provinceId, $cityId){
        $province = $this->getProvince($provinceId);
        $city = $this->getCity($province,$cityId);
        $province->setCities($city);
        return $province;
    }
}