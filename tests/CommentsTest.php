<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CommentsTest extends TestCase
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

    public function login($email,$password){
        $response=$this->action('POST','AuthenticateController@store',[
            'email'=>$email,'password'=>$password]);
        $token=\GuzzleHttp\json_decode($response->content(),true)['token'];
        return $token;
    }
    public static function createBuyer(){
        $buyer=factory(App\Buyer::class)->create();
        $user = $buyer->user()->save(factory(App\User::class)->create());
        return $user;
    }
    public function testCreateMessages()
    {
        $buyer = self::createBuyer();
        $token = $this->login($buyer->email,'secret');
        $this->refreshApplication();

        $response=$this->action('POST','CommentController@store',['token'=>$token],[
            'product_id'=>random_int(1,50),
            'body'=>\Wego\Helpers\PersianFaker::getSentence(),
        ],[],[]);

        $message=\GuzzleHttp\json_decode($response->content(),true)['message'];
        $this->assertEquals('message successfully created',$message);
    }
}
