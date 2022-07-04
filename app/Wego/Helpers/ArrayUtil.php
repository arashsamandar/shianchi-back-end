<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 10/11/16
 * Time: 10:26
 */

namespace Wego\Helpers;


use Carbon\Carbon;

class ArrayUtil
{

    /**
     * @param $string
     * @param string $delimiter
     * @return string
     * string must have template like this => ali_hosein_kazem
     * return ali_hosein
     */
    public static function removeLastNodeFromString($string,$delimiter='_')
    {
        $stringExplodedByUnderScore = explode($delimiter,$string);
        $stringExplodedByUnderScore = self::removeLastElementOfArray($stringExplodedByUnderScore);
        return self::makeArrayWithStringByGlue($stringExplodedByUnderScore);

    }

    public static function makeArrayWithStringByGlue($array=[],$glue='_')
    {
        return implode($glue,$array);
    }
    public static function removeLastElementOfArray($array)
    {
        unset($array[self::getLastIndexOfArray($array)]);
        return $array;

    }
    public static function getLastIndexOfArray($array)
    {
        return count($array) - 1;
    }


    public static function addTimeStampToSingleArray($array)
    {
        $array+= ['created_at'=>Carbon::now()->toDateTimeString(),'updated_at'=>Carbon::now()->toDateTimeString()];
        return $array;
    }

    public static  function addTimeStampToArrays($arrays)
    {
        foreach ($arrays as $key=>$array) {
            $arrays[$key]=self::addTimeStampToSingleArray($array);
        }
        return $arrays;
    }



}