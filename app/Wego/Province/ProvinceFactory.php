<?php

namespace Wego\Province;
use Wego\Province\Util\ProvinceManager;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 25/05/16
 * Time: 17:16
 */
class ProvinceFactory
{
    protected $provinceId;

    public function factory(ProvinceManager $provinceManager)
    {
        return $provinceManager->getProvince($this->provinceId)->getCities();
    }

    /**
     * @param mixed $provinceId
     * @return $this
     */
    public function setProvinceId($provinceId)
    {
        $this->provinceId = $provinceId;
        return $this;
    }
}