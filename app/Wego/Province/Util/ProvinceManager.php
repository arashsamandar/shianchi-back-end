<?php
use Wego\Province\Province;

/**
 * Created by PhpStorm.
 * User: wb-2
 * Date: 6/1/16
 * Time: 10:10 AM
 */
namespace Wego\Province\Util;

abstract class ProvinceManager
{
    /**
     * @param $id
     * @return mixed
     *
     * ostane id marboote ro barmigardoone
     */
    public abstract function getProvince($id);

    public abstract function getProvinceAndCity($provinceId,$cityId);
    /**
     * @param Province $province
     * ostane dade shode ro zakhire mikone
     */
    public function saveProvince(Province $province){

    }

    /**
     * hameie ostanha ro zakhire mikone
     */
    public function saveProvinces(){

    }
}