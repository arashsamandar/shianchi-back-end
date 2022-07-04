<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ShippingProvincesTest extends TestCase
{
    public function testProvincesJSON(){
        $provinces = json_decode(Storage::get('provinces.json'),true)['provinces'];
        foreach($provinces as $province){
            $this->assertFalse($province['id'] === null);
            foreach($province['cities'] as $city){
                $this->assertFalse($city['id'] === null);
            }
        }
    }
}
