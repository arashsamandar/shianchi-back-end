<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 1/12/16
 * Time: 6:29 PM
 */

namespace Wego\UserHandle;


use App\Store;

class SaveToStoreTable
{
    const PART = 'part';
    const BOSS_MOBILE = 'boss_mobile';
    const CARD_NUMBER = 'card_number';
    const MENU = 'menu';
    const PHONE = 'phone';
    const WORK_TIME ='work_time';
    public function storeManager(Store $store , $request)
    {
        if(array_has($request,[self::PART]))
        {
            $this->savePart($store,$request[self::PART]);
        }
        if(array_has($request,[self::BOSS_MOBILE]))
        {
            $this->saveBossMobile($store,$request[self::BOSS_MOBILE]);
        }
        if(array_has($request,[self::CARD_NUMBER]))
        {
            $this->saveCardNumber($store,$request[self::CARD_NUMBER]);
        }
        if(array_has($request,[self::MENU]))
        {
            $this->saveMenu($store,$request[self::MENU]);
        }
        if(array_has($request,[self::PHONE]))
        {
            $this->savePhone($store,$request[self::PHONE]);
        }
        if(array_has($request,[self::WORK_TIME]))
        {
            $this->saveWorkTime($store,$request[self::WORK_TIME]);
        }

    }
    private function savePart(Store $store, array $parts)
    {
        if(count($parts) > 10)
            return;
        foreach($parts as $part){
            $store->part()->attach($store,$part);
        }
    }
    private function saveBossMobile(Store $store, array $boss_mobiles)
    {
        if(count($boss_mobiles) > 10)
            return;
        foreach ($boss_mobiles as $boss_mobile) {
            $store->boss_mobile()->save($boss_mobile);
        }
    }
    private function saveCardNumber(Store $store , array $card_numbers)
    {
        if(count($card_numbers) > 10)
            return;
        foreach ($card_numbers as $card_number){
            $store->card_number()->save($card_number);
        }
    }
    private function saveMenu(Store $store, array $menus)
    {
        if(count($menus) > 10)
            return;
        foreach ($menus as $menu){
            $store->menu()->save($menu);
        }
    }
    private function savePhone(Store $store , array $phones)
    {
        if(count($phones) > 10)
            return;
        foreach ($phones as $phone){
            $store->card_number()->save($phone);
        }
    }
    private function saveWorkTime(Store $store , array $workTimes)
    {
        if(count($workTimes) > 10)
            return;
        foreach ($workTimes as $workTime){
            $store->card_number()->save($workTime);
        }
    }
}