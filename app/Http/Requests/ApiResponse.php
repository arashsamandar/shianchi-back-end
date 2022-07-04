<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 26/11/16
 * Time: 18:13
 */

namespace App\Http\Requests;


use App\Http\Controllers\ApiController;

trait ApiResponse
{
    public function response(array $errors)
    {
        return (new ApiController())->respondErrorArray($errors);
    }
}