<?php

use Elasticsearch\ClientBuilder;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MessageTest extends TestCase
{

    protected $newMessageExample=[
        "subject"=>"new meessage title template",
        "body"=>"body of template message",
        "receiver_id"=>""

    ];

    /** @test */
    public function process_of_sending_message_and_read_it(){
        $buyer=createBuyer();
        $store=createStore();
        $this->sendMessageFromBuyerToStore($buyer,$store);
        sleep(5);
        $this->refreshApplication();
        $foundMessage=$this->getTheBuyerMessageFromStoreInboxAsUnreadMessage($store,$buyer);
        $this->assertStatusIsUnreadForReceiver($foundMessage);
        $this->refreshApplication();
        $this->setTheMessageStatusToRead($foundMessage,$store);
        $this->assertResponseOk();
        sleep(2);
        $this->refreshApplication();
        $newFoundMessage=$this->getTheBuyerMessageFromStoreInbox($store,$buyer);
        $this->assertStatusSetToRead($newFoundMessage);
    }
    public function testAddReplyToMessage(){

    }
    public function testReadMessage(){

    }
    public function testDeleteMessage(){

    }
    public function testStoreWegoMessage(){

    }
    public function testReadWegoMessage(){

    }
    public function testDeleteWegoMessage(){

    }
    /** @test */
    public function get_message_when_receiver_has_no_message_must_not_give_error(){

    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $store=\App\Store::find(2000);
        dd($store == null);
    }

    /**
     * @param $buyer
     * @param $store
     */
    public function sendMessageFromBuyerToStore($buyer,$store){
        $token=$this->login($buyer->user->email,'secret');
        $messageBody=$this->newMessageExample;
        $messageBody['receiver_id']=$store->user->id;
        $this->refreshApplication();
        $this->sendMessage($token,$messageBody);
    }

    /**
     * @param $token
     * @param $messageBody
     */
    public function sendMessage($token,$messageBody){
        $this->action( 'POST','MessageController@store',['token'=>$token],$messageBody,[],[]);

    }
    /**
     * @param $store
     * @param $buyer
     * @return mixed
     */
    public function getTheBuyerMessageFromStoreInboxAsUnreadMessage($store,$buyer){
        $token=$this->login($store->user->email,'secret');
        $this->refreshApplication();
        $unreadResponse=$this->getUnreadMessages($token);
        $decodedResponse=\GuzzleHttp\json_decode($unreadResponse->content(),true);
        $foundMessage=null;
        foreach($decodedResponse['hits']['hits'] as $message){
            if($message['_source']['sender_id']==$buyer->user->id){
                $foundMessage=$message;
            }
        }
        return $foundMessage;
    }

    /**
     * @param $token
     * @return \Illuminate\Http\Response
     */
    public function getUnreadMessages($token){
        return ($this->action('GET','MessageController@getReceiverUnreadMessages',['token'=>$token],[],[]));
    }

    /**
     * @param $token
     * @return \Illuminate\Http\Response
     */
    public function getAllReceiverMessages($token){
        return ($this->action('GET','MessageController@getReceiverMessages',['token'=>$token],[],[]));
    }

    /**
     * @param $message
     * @param $owner
     */
    public function setTheMessageStatusToRead($message,$owner){
        $token=$this->login($owner->user->email,'secret');
        $this->refreshApplication();
        $this->action('POST','MessageController@readMessage',['id'=>$message['_id'],'token'=>$token],[],[]);
    }

    /**
     * @param $message
     */
    public function assertStatusSetToRead($message){
        $this->assertNotNull($message);
        $this->assertTrue($message['_source']['receiver_isRead']);
        $this->assertTrue($message['_source']['sender_isRead']);
    }

    /**
     * @param $message
     */
    public function assertStatusIsUnreadForReceiver($message){
        $this->assertNotNull($message);
        $this->assertFalse($message['_source']['receiver_isRead']);
        $this->assertTrue($message['_source']['sender_isRead']);
    }

    /**
     * @param $store
     * @param $buyer
     * @return mixed
     */
    public function getTheBuyerMessageFromStoreInbox($store,$buyer){
        $token=$this->login($store->user->email,'secret');
        $this->refreshApplication();
        $getMessages=$this->getAllReceiverMessages($token);
        $decodedMessages=\GuzzleHttp\json_decode($getMessages->content(),true);
        $newFoundMessage=null;
        foreach($decodedMessages['hits']['hits'] as $message){
            if($message['_source']['sender_id']==$buyer->user->id){
                $newFoundMessage=$message;
            }
        }
        return $newFoundMessage;
    }
}
