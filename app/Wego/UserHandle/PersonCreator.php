<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 12/27/15
 * Time: 3:04 PM
 */

namespace Wego\UserHandle;

interface PersonCreator
{
    public function create($request);
    public function delete();
    public function update(array $request , $id);
    public function show($id);
}