<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/20/16
 * Time: 5:47 PM
 */

namespace Wego\UserHandle;


class ValidateFactory
{
    public function valid(Validateable $validateable , $request)
    {
        $validateable->validate($request);
    }
}