<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 10/09/17
 * Time: 16:37
 */

namespace Wego\Transforms;


trait TransformerHelper
{

    public function transformWithFieldFilter($data, $fields)
    {
        if (is_null($fields) || empty($fields)) {
            return $data;
        }

        $fieldsArray = explode(',', $fields);

        return array_intersect_key($data, array_flip($fieldsArray));
    }

    public function getFields($owner, $fields)
    {
        $fieldsArray = explode(',', $fields);

        $ownerFields = array_filter($fieldsArray,function($value) use ($owner){
            return strpos($value,$owner) !== false;
        });

        $rawOwnerFields = array_map(function($v) use ($owner){
            return str_replace($owner,'',$v);
        },$ownerFields);

        return implode(',',$rawOwnerFields);
    }
}