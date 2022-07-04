<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 11/29/16
 * Time: 4:52 PM
 */

namespace Wego\validator;


class SpecialValidator
{
    public function validate($attribute,$rule){
        foreach($rule as $Rule){
            $isValid=false;
            switch($Rule['type']){
                case 'gift':
                    if (strlen($Rule['text'])>=5 && strlen($Rule['text'])<=100 && strlen($Rule['amount'])>=2 && strlen($Rule['amount'])<=8)
                        $isValid=true;
                    else
                        return false;
                    break;
                case 'discount':
                    if (strlen($Rule['amount'])>=1 && strlen($Rule['amount'])<=3)
                        $isValid=true;
                    else
                        return false;


            }
        }
        return $isValid;
    }

}