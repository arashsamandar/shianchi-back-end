<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Wego\Picture\Picture;

class NewPictureTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testTempPictureSave(){
        $path = (new Picture)->setPicture(UploadedFile::fake()->image('avatar.jpg'))->saveTemp(0,0,100,100);
        $this->assertTrue(is_dir($path));
    }
}
