<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tymon\JWTAuth\Facades\JWTAuth;

class PictureTest extends TestCase
{
    protected $file;
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
        "card_number"=> "5894-6318-2850-0579",
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
                "type"=> "logo",
                "path"=> ""
            ]
        ]
    ];

    protected $newProductTemplate=[

    "english_name"=>"hosegdsfsfin",
    "persian_name"=>"حrfggdgسین",
    "current_price"=>"180",
    "description"=>"azina",
    "weight"=>12,
    "warranty_name"=>"hosein",
    "warranty_text"=>"abedi",
    "wego_coin_need" => 85,
    "quantity"=>18,
    "key_name"=>"brggertgeil",
    "made_in"=>"3",
    "pictures"=>
    [
        [
            "type"=>1,
            "path"=>""
        ],
        [
            "type"=>2,
            "path"=>""
        ],
        [
            "type"=>3,
            "path"=>""
        ],
        [
            "type"=>4,
            "path"=>""
        ],
        [
            "type"=>5,
            "path"=>""
        ],
    ],
    "values"=>[1,2,3],
    "special"=>[
            [
                "type"=>"wego_coin",
                "amount"=>12,
                "expiration"=> 18,
                "text"=>null,
                "upper_value_type"=>"hosein",
                "upper_value"=>12
            ],
            [
                "type"=>"dscount",
                "amount"=>12,
                "expiration"=> 18,
                "text"=>null,
                "upper_value_type"=>"hosein",
                "upper_value"=>12
            ],
            [
                "type"=>"gift",
                "amount"=>12,
                "expiration"=> 18,
                "text"=>"yek jayeze khub",
                "upper_value_type"=>"hosein",
                "upper_value"=>12
            ]
    ],
    "colors"=>[1,2,3],
     "category_id"=>1
    ];
    protected $buyerExample=[
        "mobile_number"=>"091242474879",
        "landline_number"=>"0211111111",
        "address"=>"lavasan lavasan lavasan",
        "card_number"=>"5894-6318-2850-0579",
        "gender"=>"male",
        "image_path"=>"alaki",
        "company_name"=>"spacex",
        "magazine_subscriber"=>"0",
        "card_owner_name"=>"alexander dumas",
        "job_title" => "roftegar",
        "birthday"=>"formatesh ba khodete"
    ];

    public function setUp(){
        parent::setUp();
        $this->file=new \Illuminate\Http\UploadedFile('/home/wb-3/Downloads/1.jpeg',
            '1.jpeg','image/jpeg',filesize('/home/wb-3/Downloads/1.jpeg'),null,true);
    }

    /**
     *
     */
    public function testSaveStorePicture(){
        $staff=createStaff();
        $token=$this->login($staff->user->email,'secret');
        $this->refreshApplication();
        $tempSaveCoverPath=$this->temporarySaveStorePicture($token,$this->file,"cover");
        $tempSaveInsidePath=$this->temporarySaveStorePicture($token,$this->file,"inside");
        $tempSaveThumbnailPath=$this->temporarySaveStorePicture($token,$this->file,"thumbnail");
        $this->assertTempStorePictureSavedCorrectly($tempSaveCoverPath,$staff,"cover");
        $this->assertTempStorePictureSavedCorrectly($tempSaveInsidePath,$staff,"inside");
        $this->assertTempStorePictureSavedCorrectly($tempSaveThumbnailPath,$staff,"thumbnail");
        //todo inja betunam ye kari konam be surate random tedad axha entekhab she khub mishe
        $fields=$this->completeStoreFields($tempSaveCoverPath,$tempSaveInsidePath,$tempSaveThumbnailPath);
        $this->saveStore($fields,$token);
        $this->assertPermanentStorePicturesSavedCorrectly($fields['email'],$tempSaveCoverPath,$tempSaveInsidePath,$tempSaveThumbnailPath);
        $this->assertResponseOk();
    }

    /**
     *
     */
    public function testChangeStorePicture(){
        $staff=createStaff();
        $token=$this->login($staff->user->email,'secret');
        $this->refreshApplication();
        $tempSaveCoverPath=$this->temporarySaveStorePicture($token,$this->file,"cover");
        $tempSaveInsidePath=$this->temporarySaveStorePicture($token,$this->file,"inside");
        $tempSaveThumbnailPath=$this->temporarySaveStorePicture($token,$this->file,"thumbnail");
        $fields=$this->completeStoreFields($tempSaveCoverPath,$tempSaveInsidePath,$tempSaveThumbnailPath);
        $this->saveStore($fields,$token);
        $user=App\User::where('email','=',$fields['email'])->first();
        $store=App\Store::where('id','=',$user['userable_id'])->first();
        $token=$this->login($store->user->email,'123');
        $fields=[
          'pictures'=>[
              [
                  "type"=> "inside",
                  "path"=> $tempSaveInsidePath
              ],
              [
                  "type"=> "cover",
                  "path"=> $tempSaveCoverPath
              ],
              [
                  "type"=> "logo",
                  "path"=> $tempSaveThumbnailPath
              ]
          ]
        ];
        $oldCoverPath=\App\StorePicture::where('type','=','cover')->where('store_id','=',$store->id)->first();
        $oldInsidePath=\App\StorePicture::where('type','=','inside')->where('store_id','=',$store->id)->first();
        $oldThumbnailPath=\App\StorePicture::where('type','=','thumbnail')->where('store_id','=',$store->id)->first();
        $this->refreshApplication();
        $this->action('POST','StoreController@updatePictures',['token'=>$token],$fields,[],[]);
        $this->assertFalse(file_exists(public_path().$oldCoverPath->path));
        $this->assertFalse(file_exists(public_path().$oldInsidePath->path));
        $this->assertFalse(file_exists(public_path().$oldThumbnailPath->path));
        $store = \App\Store::find($store->id);
        $pictures=$store->pictures;
        $this->refreshApplication();
        foreach($pictures as $picture){
            $this->assertTrue(file_exists(public_path().$picture->path));
            switch($picture->type) {
                case "cover": {
                    $this->assertTrue(strpos($picture->path,
                            'wego/store/store' . $store->id .
                            '/storePicture/cover/' . basename($tempSaveCoverPath)) !== false);
                    break;
                }
                case "inside": {
                    $this->assertTrue(strpos($picture->path,
                            'wego/store/store' . $store->id .
                            '/storePicture/inside/' . basename($tempSaveInsidePath)) !== false);
                    //dd(str_replace('original','500',basename($picture->path)));
                    $this->assertTrue(file_exists(public_path() .
                        '/wego/store/store' . $store->id .
                        '/storePicture/inside/' . str_replace('original', '500', basename($tempSaveInsidePath))));
                    break;
                }
                case "thumbnail": {
                    $this->assertTrue(strpos($picture->path,
                            'wego/store/store' . $store->id .
                            '/storePicture/thumbnail/' . basename($tempSaveThumbnailPath)) !== false);
                    $this->assertTrue(file_exists(public_path() .
                        '/wego/store/store' . $store->id .
                        '/storePicture/thumbnail/' . str_replace('original', '130', basename($tempSaveThumbnailPath))));
                    break;
                }
            }
        }
        $this->assertFalse(file_exists(public_path().$tempSaveInsidePath));
        $this->assertFalse(file_exists(public_path().$tempSaveCoverPath));
        $this->assertFalse(file_exists(public_path().$tempSaveThumbnailPath));

    }
    public function testDeleteStorePicture(){
        $staff=createStaff();
        $token=$this->login($staff->user->email,'secret');
        $this->refreshApplication();
        $tempSaveCoverPath=$this->temporarySaveStorePicture($token,$this->file,"cover");
        $tempSaveInsidePath=$this->temporarySaveStorePicture($token,$this->file,"inside");
        $tempSaveThumbnailPath=$this->temporarySaveStorePicture($token,$this->file,"thumbnail");
        $fields=$this->completeStoreFields($tempSaveCoverPath,$tempSaveInsidePath,$tempSaveThumbnailPath);
        $this->saveStore($fields,$token);
        $user=App\User::where('email','=',$fields['email'])->first();
        $store=App\Store::where('id','=',$user['userable_id'])->first();
        $token=$this->login($store->user->email,'123');
        $coverPath=\App\StorePicture::where('type','=','cover')->where('store_id','=',$store->id)->first();
        $insidePath=\App\StorePicture::where('type','=','inside')->where('store_id','=',$store->id)->first();
        $thumbnailPath=\App\StorePicture::where('type','=','logo')->where('store_id','=',$store->id)->first();
        $this->refreshApplication();
        $this->deleteStorePicture($coverPath->path,$token);
        $this->deleteStorePicture($insidePath->path,$token);
        $this->deleteStorePicture($thumbnailPath->path,$token);
        $this->assertStorePictureDeleted($store,$coverPath,'cover');
        $this->assertStorePictureDeleted($store,$insidePath,'inside');
        $this->assertStorePictureDeleted($store,$thumbnailPath,'thumbnail');
    }
    public function testSaveProductPicture(){
        $store=\App\Store::inRandomOrder()->first();
        while(! password_verify('123',$store->user->password)){
            $store=App\Store::inRandomOrder()->first();
        }
        $this->refreshApplication();
        $response=$this->action('POST','AuthenticateController@store',[
            'email'=>$store->user->email,'password'=>'123']);
        $token=\GuzzleHttp\json_decode($response->content(),true)['token'];
        $file=new \Illuminate\Http\UploadedFile('/home/wb-3/Downloads/1.jpeg','1.jpeg','image/jpeg',
            filesize('/home/wb-3/Downloads/1.jpeg'),null,true);
        $this->refreshApplication();
        $tempSavePictureResponse=$this->action('POST','ProductPictureController@store',['x' => '100','y' => '200',
            'width' => '750','height'=> '750','type'=>'1','token'=>$token],[],[],['pic'=>$file]);
        copy(public_path().$tempSavePictureResponse->content(),'/home/wb-3/Downloads/1.jpeg');
        $this->refreshApplication();
        $tempSavePictureResponse2=$this->action('POST','ProductPictureController@store',['x' => '100','y' => '200',
            'width' => '750','height'=> '750','type'=>'2','token'=>$token],[],[],['pic'=>$file]);
        copy(public_path().$tempSavePictureResponse->content(),'/home/wb-3/Downloads/1.jpeg');
        $this->refreshApplication();
        $tempSavePictureResponse3=$this->action('POST','ProductPictureController@store',['x' => '100','y' => '200',
            'width' => '750','height'=> '750','type'=>'3','token'=>$token],[],[],['pic'=>$file]);
        copy(public_path().$tempSavePictureResponse->content(),'/home/wb-3/Downloads/1.jpeg');
        $this->refreshApplication();
        $tempSavePictureResponse4=$this->action('POST','ProductPictureController@store',['x' => '100','y' => '200',
            'width' => '750','height'=> '750','type'=>'4','token'=>$token],[],[],['pic'=>$file]);
        copy(public_path().$tempSavePictureResponse->content(),'/home/wb-3/Downloads/1.jpeg');
        $this->refreshApplication();
        $tempSavePictureResponse5=$this->action('POST','ProductPictureController@store',['x' => '100','y' => '200',
            'width' => '750','height'=> '750','type'=>'5','token'=>$token],[],[],['pic'=>$file]);
        copy(public_path().$tempSavePictureResponse->content(),'/home/wb-3/Downloads/1.jpeg');
        $this->assertTrue(file_exists(public_path().$tempSavePictureResponse->content()));
        $this->assertTrue(file_exists(public_path().$tempSavePictureResponse2->content()));
        $this->assertTrue(file_exists(public_path().$tempSavePictureResponse3->content()));
        $this->assertTrue(file_exists(public_path().$tempSavePictureResponse4->content()));
        $this->assertTrue(file_exists(public_path().$tempSavePictureResponse5->content()));
        $fields=$this->newProductTemplate;
        $fields['pictures'][0]['path']=$tempSavePictureResponse->content();
        $fields['pictures'][1]['path']=$tempSavePictureResponse2->content();
        $fields['pictures'][2]['path']=$tempSavePictureResponse3->content();
        $fields['pictures'][3]['path']=$tempSavePictureResponse4->content();
        $fields['pictures'][4]['path']=$tempSavePictureResponse5->content();
        $this->refreshApplication();
        $this->action('POST','ProductController@store',['token'=>$token],$fields,[],[]);
        $this->assertFalse(file_exists(public_path().$tempSavePictureResponse->content()));
        $this->assertFalse(file_exists(public_path().$tempSavePictureResponse2->content()));
        $this->assertFalse(file_exists(public_path().$tempSavePictureResponse3->content()));
        $this->assertFalse(file_exists(public_path().$tempSavePictureResponse4->content()));
        $this->assertFalse(file_exists(public_path().$tempSavePictureResponse5->content()));
        $product=App\Product::orderBy('created_at','desc')->first();
        $pictures=$product->pictures;
        foreach($pictures as $picture){
            $this->assertTrue(file_exists(public_path().$picture->path));
            $this->assertTrue(strpos($picture->path,'/wego/store/store'.$store->id.'/product/product'.$product->id.'/'.$picture->type) !==false);
            $this->assertTrue(file_exists(public_path(). str_replace('original', '150',$picture->path)));
            $this->assertTrue(file_exists(public_path(). str_replace('original', '250',$picture->path)));
        }


    }
    public function testChangeProductPicture(){
        $product=\App\Product::inRandomOrder()->first();
        while($product->pictures->isEmpty() or !password_verify('123',$product->store->user->password)){
            $product=\App\Product::inRandomOrder()->first();
        }
        $store=$product->store;
        $this->refreshApplication();
        $response=$this->action('POST','AuthenticateController@store',[
            'email'=>$store->user->email,'password'=>'123']);
        $token=\GuzzleHttp\json_decode($response->content(),true)['token'];
        $file=new \Illuminate\Http\UploadedFile('/home/wb-3/Downloads/1.jpeg','1.jpeg','image/jpeg',
            filesize('/home/wb-3/Downloads/1.jpeg'),null,true);
        $this->refreshApplication();
        $tempSavePictureResponse=$this->action('POST','ProductPictureController@store',['x' => '100','y' => '200',
            'width' => '750','height'=> '750','type'=>'1','token'=>$token],[],[],['pic'=>$file]);
        copy(public_path().$tempSavePictureResponse->content(),'/home/wb-3/Downloads/1.jpeg');
        $tempSavePictureResponse2=$this->action('POST','ProductPictureController@store',['x' => '100','y' => '200',
            'width' => '750','height'=> '750','type'=>'2','token'=>$token],[],[],['pic'=>$file]);
        copy(public_path().$tempSavePictureResponse->content(),'/home/wb-3/Downloads/1.jpeg');
        $tempSavePictureResponse3=$this->action('POST','ProductPictureController@store',['x' => '100','y' => '200',
            'width' => '750','height'=> '750','type'=>'3','token'=>$token],[],[],['pic'=>$file]);
        copy(public_path().$tempSavePictureResponse->content(),'/home/wb-3/Downloads/1.jpeg');
        $tempSavePictureResponse4=$this->action('POST','ProductPictureController@store',['x' => '100','y' => '200',
            'width' => '750','height'=> '750','type'=>'4','token'=>$token],[],[],['pic'=>$file]);
        copy(public_path().$tempSavePictureResponse->content(),'/home/wb-3/Downloads/1.jpeg');
        $tempSavePictureResponse5=$this->action('POST','ProductPictureController@store',['x' => '100','y' => '200',
            'width' => '750','height'=> '750','type'=>'5','token'=>$token],[],[],['pic'=>$file]);
        copy(public_path().$tempSavePictureResponse->content(),'/home/wb-3/Downloads/1.jpeg');
        $this->assertTrue(file_exists(public_path().$tempSavePictureResponse->content()));
        $this->assertTrue(file_exists(public_path().$tempSavePictureResponse2->content()));
        $this->assertTrue(file_exists(public_path().$tempSavePictureResponse3->content()));
        $this->assertTrue(file_exists(public_path().$tempSavePictureResponse4->content()));
        $this->assertTrue(file_exists(public_path().$tempSavePictureResponse5->content()));
        $fields=$this->newProductTemplate;
        $fields['pictures'][0]['path']=$tempSavePictureResponse->content();
        $fields['pictures'][1]['path']=$tempSavePictureResponse2->content();
        $fields['pictures'][2]['path']=$tempSavePictureResponse3->content();
        $fields['pictures'][3]['path']=$tempSavePictureResponse4->content();
        $fields['pictures'][4]['path']=$tempSavePictureResponse5->content();
        $oldPictures=$product->pictures;

        $this->action('POST','ProductController@customUpdate',['id'=>$product->id,'token'=>$token],$fields,[],[]);
        $product=\App\Product::find($product->id);
        foreach($oldPictures as $oldpicture){
            $this->assertFalse(file_exists(public_path().$oldpicture->path));
        }
        $newPictures=$product->pictures;
        foreach($newPictures as $newpicture){
            //dd($picture->path."         ".$picture->type);
            $this->assertTrue(file_exists(public_path().$newpicture->path));
            $this->assertTrue(strpos($newpicture->path,'/wego/store/store'.$store->id.'/product/product'.$product->id.'/'.$newpicture->type) !==false);
            $this->assertTrue(file_exists(public_path(). str_replace('original', '150',$newpicture->path)));
            $this->assertTrue(file_exists(public_path(). str_replace('original', '250',$newpicture->path)));
        }

    }
    public function testDeleteProductPicture(){
        $product=\App\Product::inRandomOrder()->first();
        while ( $product->pictures->isEmpty() or ! password_verify('123',$product->store->user->password)){
            $product=\App\Product::inRandomOrder()->first();
        }
        $store=$product->store;
        $response=$this->action('POST','AuthenticateController@store',[
            'email'=>$store->user->email,'password'=>'123']);
        $token=\GuzzleHttp\json_decode($response->content(),true)['token'];
        $file=new \Illuminate\Http\UploadedFile('/home/wb-3/Downloads/1.jpeg','1.jpeg','image/jpeg',
            filesize('/home/wb-3/Downloads/1.jpeg'),null,true);
        $this->refreshApplication();
        foreach($product->pictures as $picture){
            $this->action('POST','ProductController@deletePicture',['token'=>$token],['path'=>$picture->path],[],[]);
            $path=$picture->path;
            $this->assertFalse(file_exists(public_path().$path));
            $this->assertFalse(file_exists(public_path().str_replace('original', '150',$path)));
            $this->assertFalse(file_exists(public_path().str_replace('original', '250',$path)));

        }
        $product=\App\Product::find($product->id);
        $this->assertTrue($product->pictures->isEmpty());
       // 'ProductController@deletePicture'

    }
    /** @test */
    public function update_department_manager_picture(){

    }
    /** @test */
    public function delete_department_manager_picture(){

    }

    /** @test */
    public function delete_store_manager_picture(){

    }

    /** @test */
    public function update_store_manager_picture(){

    }
    public function testExample()
    {
        $this->assertTrue(true);
    }



    public function temporarySaveStorePicture($token,$file,$type){
        $this->refreshApplication();
        $tempSaveCoverResponse=$this->action('POST','StorePictureController@store',['x' => '0','y' => '0',
            'width' => '100','height'=> '100','type'=>$type,'token'=>$token],[],[],['pic'=>$file]);
        $path= (\GuzzleHttp\json_decode($tempSaveCoverResponse->content())->{'path'});
        $this->moveTheTestPicToDefaultPlace($path);
        return $path;
    }


    /**
     * @param $tempPath
     * @param $staff
     * @param $type
     */
    public function assertTempStorePictureSavedCorrectly($tempPath,$staff,$type){
        $this->assertTrue(file_exists(public_path().$tempPath));
        $this->assertTrue(strpos($tempPath,
                '/wego/staff/staff'.$staff->id.'/temp/'.$type.'/'.basename($tempPath))!==false);
    }

    /**
     * @param $movedPath
     */
    public function moveTheTestPicToDefaultPlace($movedPath){
        copy(public_path().$movedPath,'/home/wb-3/Downloads/1.jpeg');
    }

    public function completeStoreFields($coverPath,$insidePath,$thumbnailPath){
        $fields=$this->newStoreExample;
        $fields['pictures'][0]['path']=$insidePath;
        $fields['pictures'][1]['path']=$coverPath;
        $fields['pictures'][2]['path']=$thumbnailPath;
        $faker=Faker\Factory::create();
        $email=$faker->email;
        $fields['email']=$email;
        return $fields;
    }

    public function saveStore($fields,$token){
        $this->action('POST','StoreController@store',['token'=>$token],$fields,[],[]);
    }

    public function assertPermanentStorePicturesSavedCorrectly($email,$tempSaveCoverPath,$tempSaveInsidePath,$tempSaveThumbnailPath){
        $user=App\User::where('email','=',$email)->first();
        $store=App\Store::where('id','=',$user['userable_id'])->first();
        $pictures=$store->pictures;
        foreach($pictures as $picture){
            $this->assertTrue(file_exists(public_path().$picture->path));
            switch($picture->type) {
                case "cover": {
                    $this->assertTrue(strpos($picture->path,
                            'wego/store/store' . $store->id . '/storePicture/cover/' . basename($tempSaveCoverPath)) !== false);
                    break;

                }
                case "inside": {
                    $this->assertTrue(strpos($picture->path,
                            'wego/store/store' . $store->id . '/storePicture/inside/' . basename($tempSaveInsidePath)) !== false);
                    $this->assertTrue(file_exists(public_path() .
                        '/wego/store/store' . $store->id . '/storePicture/inside/' . str_replace('original', '500', basename($tempSaveInsidePath))));
                    break;
                }
                case "logo": {
                    $this->assertTrue(strpos($picture->path,
                            'wego/store/store' . $store->id . '/storePicture/thumbnail/' . basename($tempSaveThumbnailPath)) !== false);
                    $this->assertTrue(file_exists(public_path() .
                        '/wego/store/store' . $store->id . '/storePicture/thumbnail/' . str_replace('original', '130', basename($tempSaveThumbnailPath))));
                    break;
                }
            }
        }
        $this->assertFalse(file_exists(public_path().$tempSaveInsidePath));
        $this->assertFalse(file_exists(public_path().$tempSaveCoverPath));
        $this->assertFalse(file_exists(public_path().$tempSaveThumbnailPath));
    }

    public function deleteStorePicture($path,$token){
        $this->action('POST','StoreController@deletePicture',['token'=>$token],['path'=>$path],[],[]);
    }

    public function assertStorePictureDeleted($store,$picture,$type){
        $this->assertFalse(file_exists(public_path().$picture->path));
        $newPath=App\StorePicture::where('type','=',$type)->where('store_id','=',$store->id)->first();
        $this->assertNull($newPath);
    }

}
