<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 26/05/16
 * Time: 09:39
 */

namespace Wego\Province;


class Province
{
    protected $name;
    protected $id;
    protected $cities=[];

    /**
     * Province constructor.
     * @param $name
     * @param $id
     * @param array $cities
     */
    public function __construct(){
        return $this;
    }

    /**
     * @return array
     */
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * @param array $cities
     */
    public function setCities($cities)
    {
        $this->cities = $cities;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getCityById($id){
        foreach($this->cities as $city){
            if($city['id'] == $id){
                return $city;
            }
        }
        return null;
    }
    public function toJson()
    {
        return [
            "name" => $this->name,
            "id" => $this->id,
            "cities" => $this->cities
        ];
    }
//    public function getConvertedToJSON(){
//        $array = array();
//        $array['name'] = $this->name;
//        $array['id'] = $this->id;
//        $array['cities'] = $this->getCities();
//        return json_encode($array);
//    }
}