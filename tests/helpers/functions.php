<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 12/4/16
 * Time: 5:16 PM
 */
/**
 * @return mixed
 */
function createBuyer(){
    $buyer=factory(App\Buyer::class)->create();
    $buyer->user()->save(factory(App\User::class)->create());
    return $buyer;
}

/**
 * @return mixed
 */
function createStore(){
    $store=factory(\App\Store::class)->create();
    $store->user()->save(factory(App\User::class)->create());
    return $store;
}
/**
 * @return mixed
 */
function createStaff(){
    $staff=factory(App\Staff::class)->create();
    $staff->user()->save(factory(App\User::class)->create());
    return $staff;
}
/**
 * Call protected/private method of a class.
 *
 * @param object &$object    Instantiated object that we will run method on.
 * @param string $methodName Method name to call
 * @param array  $parameters Array of parameters to pass into method.
 *
 * @return mixed Method return.
 */
function invokeMethod(&$object, $methodName, array $parameters = array())
{
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);

    return $method->invokeArgs($object, $parameters);
}

