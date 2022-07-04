<?php
/**
 * Created by PhpStorm.
 * User: wb-2
 * Date: 6/12/16
 * Time: 1:37 PM
 */

namespace Wego\Helpers;


class JsonUtil
{
    public static function convertKeys($array,$keysMap){
        foreach ($keysMap as $new => $old) {
            if(array_key_exists($old,$array)){
                $array[$new] = $array[$old];
                unset($array[$old]);
            }
        }
        return $array;
    }

    public static function removeFields($json,$fieldNames){
        foreach($fieldNames as $fieldName){
            if(strpos($fieldName,"*") === false){
                self::removeNonArrayField($json,$fieldName);
            }else{
                self::removeArrayField($json,$fieldName);
            }
        }
        return $json;
    }

    private static function removeNonArrayField(&$json,$fieldName){
        $explodedAttribute = explode('.',$fieldName);
        $target = &$json;
        for($i = 0 ; $i < count($explodedAttribute) - 1 ; $i++){
            $target = &$target[$explodedAttribute[$i]];
        }
        unset($target[$explodedAttribute[count($explodedAttribute) - 1]]);
    }

    private static function removeArrayField(&$json,$fieldName){
        $explodedAttribute = explode('.',$fieldName);
        $target = &$json;
        for($i = 0 ; $i < count($explodedAttribute) - 1 ; $i++){
            if(!strcmp("*",$explodedAttribute[$i])){
                foreach($target as &$star){
                    $star = self::removeFields($star,[join(".",array_slice($explodedAttribute,$i+1,count($explodedAttribute)))]);
                }
                break;
            }else{
                $target = &$target[$explodedAttribute[$i]];
            }
        }
    }
}