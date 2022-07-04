<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/14/16
 * Time: 7:32 PM
 */

namespace Wego\Store;


use App\Store;

interface StorePivotHandlerInterface
{
    public function save(array $requests, Store $store);
}