<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Wego\Buy\OrderProduct\WegoCoinHandler;

class WegoCoinHandlerTest extends TestCase
{
    const  USER_ID = 23;
    protected $WEGO_COIN = [
        0=>[
            "id" => 32,
            "expiration" => "0000-00-00 00:00:00",
            "user_id" => "23",
            "store_id" => "11",
            "amount" => 18,
            "status" => "a",
            "created_at" => "2016-07-26 14:35:39",
            "updated_at" => "2016-07-26 14:35:39",
        ],
        1 => [
            "id" => 31,
            "expiration" => "2016-07-25 00:00:00",
            "user_id" => "23",
            "store_id" => "11",
            "amount" => 4,
            "status" => "a",
            "created_at" => "2016-07-25 11:32:02",
            "updated_at" => "2016-07-25 11:32:02",
        ]];
    const WEGO_COIN_NEED = 7;
    const QUANTITY = 4;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSubtractedAmount()
    {
        $sumOfWegoCoin = $this->sum($this->WEGO_COIN,'amount');
        $result = WegoCoinHandler::calculateWegoCoinUse($this->WEGO_COIN,self::WEGO_COIN_NEED,self::QUANTITY);
        $this->assertEquals(min($sumOfWegoCoin,self::QUANTITY * self::WEGO_COIN_NEED),$this->sum($result,'subtracted_amount'));
    }

    public function sum($array,$field){
        return array_sum(array_column($array,$field));
    }
}
