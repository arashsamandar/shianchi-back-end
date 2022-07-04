<?php
/**
 * Created by PhpStorm.
 * User: wb-2
 * Date: 5/31/16
 * Time: 5:47 PM
 */

namespace Wego\Shipping\Price;


use App\BuyerAddress;
use Exception;
use Wego\Province\Util\MemoryProvinceManager;

abstract class ShippingPrice
{
    const TEHRAN_ID = 1;
    const TEHRAN_CITY_ID = 1698;
    const FREE = 'free';
    const NOT_FREE = 'not_free';
    protected $weight;
    protected $address;
    protected $sourceProvinceId = MemoryProvinceManager::TEHRAN_PROVINCE_ID;
    protected $sourceCityId = MemoryProvinceManager::TEHRAN_CITY_ID;

    /**
     * @return mixed
     */
    public function getSourceCityId()
    {
        return $this->sourceCityId;
    }

    /**
     * @return mixed
     */
    public function getDestinationCityId()
    {
        return $this->address->city_id;
    }


    public abstract function get();

    /**
     * @return mixed
     */
    public function getDestinationProvinceId()
    {
        return $this->address->province_id;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return mixed
     */
    public function getSourceProvinceId()
    {
        return $this->sourceProvinceId;
    }


    /**
     * @param $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function checkIfVariablesIsSet()
    {
        $this->checkIfVariableIsSet($this->weight, "weight");
        $this->checkIfVariableIsSet($this->sourceProvinceId, "sourceProvinceId");
        $this->checkIfVariableIsSet($this->sourceCityId, "sourceCityId");
    }

    /**
     * @param $variable
     * @param $variableName
     * @throws Exception
     */
    public function checkIfVariableIsSet($variable, $variableName)
    {
        if ($variable === null) {
            $exceptionMessage = "variable " . $variableName . " has not been set";
            throw new Exception($exceptionMessage);
        }
    }

    /**
     * @param mixed $address
     * @return ShippingPrice
     */
    public function setAddress(BuyerAddress $address)
    {
        $this->address = $address;
        return $this;
    }
}