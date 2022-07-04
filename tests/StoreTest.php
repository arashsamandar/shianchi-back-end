<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StoreTest extends TestCase
{
    protected $newStoreExample=[
        "persian_name"=> "asdf",
        "english_name"=> "asdf",
        "email"=>"",
        "password"=> "123",
        "about_us"=> "asdfasdfasdfas",
        "password_confirmation"=> "123",
        "business_license"=> "125151561",
        "province_id"=> "3",
        "city_id"=> "1439",
        "bazaar"=> "1",
        "address"=> "asdfasdfasdfsadfasdf asdfasdf asdfasdf",
        "shaba_number"=> "165165165165",
        "fax_number"=> "0212121221",
        "information"=> "asdfasdfasdfsadf",
        "account_number"=> "15165165165",
        "card_number"=> "1651-6516-6515-5615",
        "card_owner_name"=> "ahmad illoo",
        "manager_first_name"=> "ahmad",
        "manager_last_name"=> "illoo",
        "manager_national_code"=> "1111111111",
        "wego_expiration"=> 3650,
        "location"=>[
            "lat"=> 35.90443496731149,
            "long"=> 51.32636522143548,
        ],
        "manager_mobile"=> [
            [
                "prefix_phone_number"=> "0222",
                "phone_number"=> "2555555",
                "id"=> 0
            ]
        ],
        "departments"=> [
            [
                "department_prefix_phone_number" => "021",
                "department_phone_number" => "21212211",
                "department_email" => "ahmad@gmail.com",
                "department_manager_first_name" => "ahmad",
                "department_manager_last_name" => "illoo",
                "department_manager_picture" => "#",
                "department_id" => "2"
            ],
        ],
        "phone"=> [
            [
                "prefix_phone_number"=> "651",
                "phone_number"=> "56651651",
                "id"=> 0
            ]
        ],
        "manager_picture"=> "$",
        "work_time"=> [
            [
                "day"=> "شنبه",
                "opening_time"=> 8,
                "closing_time"=> 17
            ],
            [
                "day"=> "یکشنبه",
                "opening_time"=> 8,
                "closing_time"=> 17
            ],
            [
                "day"=> "دوشنبه",
                "opening_time"=> 8,
                "closing_time"=> 17
            ],
            [
                "day"=> "سه شنبه",
                "opening_time"=> 8,
                "closing_time"=> 17
            ],
            [
                "day"=> "چهارشنبه",
                "opening_time"=> 8,
                "closing_time"=> 17
            ],
            [
                "day"=> "پنج شنبه",
                "opening_time"=> 8,
                "closing_time"=> 17
            ],
            [
                "day"=> "جمعه",
                "opening_time"=> 8,
                "closing_time"=> 17
            ]
        ],
        "pictures"=> [
            [
                "type"=> "inside",
                "path"=> ""
            ],
            [
                "type"=> "cover",
                "path"=> ""
            ],
            [
                "type"=> "thumbnail",
                "path"=> ""
            ]
        ]
    ];

    public function testSaveStore(){
        $staff=$this->createStaff();
        $token=$this->login($staff->user->email,'secret');
        $fields=$this->newStoreExample;
        $faker=Faker\Factory::create();
        $email=$faker->email;
        $fields['email']=$email;
        $this->refreshApplication();
        $result=$this->action('POST','StoreController@store',['token'=>$token],$fields,[],[]);;
        $this->seeInDatabase('users',['email'=>$email]);
        $user=\App\User::where('email','=',$email)->first();
        $store=\App\Store::find($user->userable_id);
        $this->seeInDatabase('department_store',['store_id'=>$store->id,'department_id'=>2]);
        $this->seeInDatabase('work_times',['store_id'=>$store->id]);
        $this->seeInDatabase('store_pictures',['store_id'=>$store->id]);
        $this->seeInDatabase('manager_mobiles',['store_id'=>$store->id]);
        $this->seeInDatabase('store_phones',['store_id'=>$store->id]);

    }

    public function testEditStore(){

    }

    public function testDeleteStore(){

    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }
}
