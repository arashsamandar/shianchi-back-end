<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BuyerPictureTest extends TestCase
{
    protected $file;
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
    public function testSaveBuyerPicture(){
        $buyer=createBuyer();
        $token=$this->login($buyer->user->email,'secret');
        $this->refreshApplication();
        $tempSavePath=$this->temporarySaveBuyerPicture($token,$this->file);
        $this->assertTempBuyerPictureSavedCorrectly($tempSavePath,$buyer);
        $this->saveBuyerPicturePermanently($token,$tempSavePath);
        $this->assertResponseOk();
        $this->assertPermanentBuyerPictureSavedCorrectly($buyer,$tempSavePath);
    }

    /**
     *
     */
    public function testChangeBuyerPicture(){
        $buyer=createBuyer();
        $token=$this->login($buyer->user->email,'secret');
        $tempSavePath=$this->temporarySaveBuyerPicture($token,$this->file);
        $this->saveBuyerPicturePermanently($token,$tempSavePath);
        $tempSavePath=$this->temporarySaveBuyerPicture($token,$this->file);
        $oldImagePath=\App\Buyer::where('id',$buyer->id)->select('image_path')->first()->toArray();
        $this->deleteBuyerPicture($token);
        $this->updateBuyerPicture($token,$tempSavePath);
        $this->assertPermanentBuyerPictureSavedCorrectly($buyer,$tempSavePath);

    }

    /**
     *
     */
    public function testDeleteBuyerPicture(){
        $buyer=createBuyer();
        $token=$this->login($buyer->user->email,'secret');
        $tempSavePath=$this->temporarySaveBuyerPicture($token,$this->file);
        $this->moveTheTestPicToDefaultPlace($tempSavePath);
        $this->saveBuyerPicturePermanently($token,$tempSavePath);
        $imagePath=\App\Buyer::where('id',$buyer->id)->select('image_path')->first()->toArray();
        $this->deleteBuyerPicture($token);
        $this->assertPictureDeleted($buyer,$imagePath);
    }

    /**
     * @param $token
     * @param $file
     * @return mixed
     */
    public function temporarySaveBuyerPicture($token,$file){
        $tempSaveResponse=$this->action('POST','BuyerController@tempStorePicture',
            ['token'=>$token],[],[],['pic'=>$file]);
        $path= (\GuzzleHttp\json_decode($tempSaveResponse->content())->{'path'});
        $this->moveTheTestPicToDefaultPlace($path);
        return $path;
    }

    /**
     * @param $tempSavePath
     * @param $buyer
     */
    public function assertTempBuyerPictureSavedCorrectly($tempSavePath,$buyer){
        $this->assertTrue(strpos($tempSavePath,
                '/wego/buyer/buyer'.$buyer->id.'/temp/'.basename($tempSavePath))!==false);
        $this->assertTrue(file_exists(public_path().$tempSavePath));
    }

    /**
     * @param $token
     * @param $tempSavePath
     */
    public function saveBuyerPicturePermanently($token,$tempSavePath){
        $this->action('POST','BuyerController@savePicture',
            ['token'=>$token],['image_path'=>$tempSavePath],[],[]);
    }

    /**
     * @param $buyer
     * @param $tempSavePath
     */
    public function assertPermanentBuyerPictureSavedCorrectly($buyer,$tempSavePath){
        $imagePath=\App\Buyer::where('id',$buyer->id)->select('image_path')->first()->toArray();
        $this->assertNotNull($imagePath['image_path']);
        $this->assertTrue(file_exists(public_path().$imagePath['image_path']));
        $this->assertFalse(file_exists(public_path().$tempSavePath));
        $this->assertTrue(strpos($imagePath['image_path'],
                '/wego/buyer/buyer'.$buyer->id.'/'.basename($tempSavePath)) !==false);
    }

    /**
     * @param $movedPath
     */
    public function moveTheTestPicToDefaultPlace($movedPath){
        copy(public_path().$movedPath,'/home/wb-3/Downloads/1.jpeg');
    }

    /**
     * @param $token
     */
    public function deleteBuyerPicture($token){
        $this->action('POST','BuyerController@deletePicture',['token'=>$token],[],[],[]);
    }
    /**
     * @param $buyer
     * @param $oldImagePath
     */
    public function assertPictureDeleted($buyer,$oldImagePath){
        $this->assertFalse(file_exists(public_path().$oldImagePath['image_path']));
        $imagePath=\App\Buyer::where('id',$buyer->id)->select('image_path')->first()->toArray();
        $this->assertNull($imagePath['image_path']);
    }
    /**
     * @param $token
     * @param $tempSavePath
     */
    public function updateBuyerPicture($token,$tempSavePath){
        $fields=$this->buyerExample;
        $fields['image_path']=$tempSavePath;
        $this->action('POST','BuyerController@Update',['token'=>$token],$fields,[],[]);
    }
}
