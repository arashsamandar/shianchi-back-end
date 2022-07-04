<?php
/**
 * Created by PhpStorm.
 * User: wb-2
 * Date: 7/21/16
 * Time: 9:00 AM
 */

namespace App\Exceptions;


use Mockery\Exception;

class BadRequestException extends Exception
{

    /**
     * BadRequestException constructor.
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }
}