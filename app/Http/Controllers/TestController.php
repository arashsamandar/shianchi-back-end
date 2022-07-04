<?php

namespace App\Http\Controllers;

use Faker\Generator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Kavenegar\KavenegarApi;
use Wego\Services\Notification\SmsNotifier;
use Elasticsearch\ClientBuilder;
use Faker\Factory as Faker;

class TestController extends Controller
{
    protected $smsNotifier;
    protected $elasticSeach;

    public $user_email;

    public function __construct(SmsNotifier $smsNotifier)
    {
        $this->elasticSeach = ClientBuilder::create()->build();
//        $this->smsNotifier = $smsNotifier;
    }

    public function elasticSearchTest() {
//        dump($this->elasticSeach);
//        echo "Retrieve a Document";
//        echo "<br>";
//        $params = [
//            'index' => 'wego_1',
//            'type' => 'products',
//            'id' => 1
//        ];
//        $response = $this->elasticSeach->get($params);
//        dump($response);
    }

    public function elasticsearchData() {
//        $params = [
//            'index' => 'pets',
//            'type' => 'birds',
//            'body' => [
//                'bird' => [
//                    '_source' => [
//                        'enabled' => true,
//                    ],
//                    'properties' => [
//                        'name' => array('type','string'),
//                        'age' => array('type','long'),
//                        'gender' => array('type','string'),
//                        'color' => array('type','string'),
//                        'braveBird' => array('type','boolean'),
//                    ]
//                ]
//            ]
//        ];
    }

    public function elasticsearchQueries() {
//        $params = [
//            'index' => 'pets',
//            'type' => 'dog',
//            'body' => [
//                'query' => [
//                    'match' => [
//                        'name' => 'susan'
//                    ]
//                ]
//            ]
//        ];
//
//        $response = $this->elasticSeach->search($params);
//        dump($response);
    }

    public function createWithBulk() {
//        $faker = Faker::create();
//        $params = [];
//
//        for($i=0;$i<100;$i++) {
//            $params['body'][] = [
//                'index' => [
//                    '_index' => 'pets',
//                    '_type' => 'dog',
//                ]
//            ];
//            $gender = $faker->randomElement(['male','female']);
//            $goodDog = $faker->randomElement([true,false]);
//            $age = $faker->numberBetween(1,15);
//            $params['body'][] = [
//                'name' => $faker->name($gender),
//                'age' => $age,
//                'gender' => $gender,
//                'color' => $faker->safeColorName,
//                'goodDog' => $goodDog
//            ];
//        }
//
//        $response = $this->elasticSeach->bulk($params);
//        dump($response);
        // after this let us take a look to see how to get an specific id or an item by query .
        // let go ... :)
    }

    public function getItemById($id) {
        $params = [
            'index' => 'wego_1',
            'type' => 'products',
            'id' => $id,
        ];
        $response = $this->elasticSeach->get($params);
        return $response;
    }

    public function getItemByName($name) {
        $params = [
            'index' => 'wego_1',
            'type' => 'products',
            'body' => [
                'query' => [
                    'match' => [
                        'persian_name' => $name
                    ]
                ]
            ]
        ];
        $response = $this->elasticSeach->search($params);
        return $response;
    }

    public function sendingSMS() {
        $sender = "10008445";
        $reviever = "09366634553";
        $sender = new \stdClass();
        $sender->id = "2132142";
//        $message = "این یک آزمایش ساده است";
//        // the API : 6E49654538343844782B582B426E374F456C464D366838394441795477502B4D2F7538666B747A74714E383D
//        $client = new KavenegarApi(env('SMS_API_KEY'));
//        $client->VerifyLookup("09366634553","353434",null,null,"onOrder","sms");
        $this->smsNotifier->sendOrder($reviever,$sender);
    }

    public function sendEmail(Request $request) {
        $this->user_email = $request->input('email');
        $resetLink = "http://shiii.ir/reset-password?token=";
        $userid = 12343456;
        Mail::send('email.shipment',['user_link'=>$resetLink,'user_id'=>$userid],function($message){
            $message->to($this->user_email)->subject(
                "سفارش ما ثبت شد"
            );
        });
        //todo:now we would set ourself an email .
    }

}
