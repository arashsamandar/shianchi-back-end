<?php
namespace Wego\validator;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 22/11/16
 * Time: 10:56
 */
class CardValidator
{

    const CARD_DIGIT_SIZE =16;
    public function validate($attribute, $rule)
    {
        $isValid = false;
        $numbers=[];
        $oddNumbers = 0;
        $evenNumbers = 0;
        $cardNumber = preg_replace('/(-)+/','',$rule);
        if(strlen($cardNumber) !== self::CARD_DIGIT_SIZE )
            return false;
        for ($i = strlen($cardNumber)-1 ; $i >=0; $i--)
        {
            $numbers[$i]= $cardNumber[$i];
        }
        for ($i = 0; $i < self::CARD_DIGIT_SIZE; $i+=2)
        {
            $multiplication = $numbers[$i]*2;
            if ($multiplication>9) {
                $oddNumbers += ($multiplication -9);
            }
            else
            {
                $oddNumbers += $multiplication ;
            }
        }
        for ($i = 1; $i<self::CARD_DIGIT_SIZE; $i+=2)
        {
            $evenNumbers += $numbers[$i]*1;
        }
        $result = $oddNumbers + $evenNumbers;
        if(($result%10) == 0)
        {
            $isValid = true;
        }
        return $isValid;
    }
}