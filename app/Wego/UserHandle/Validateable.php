<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/20/16
 * Time: 5:38 PM
 */

namespace Wego\UserHandle;


interface Validateable
{
    public function validate($request);
}