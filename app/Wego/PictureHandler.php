<?php

/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 10/16/16
 * Time: 2:51 PM
 */
namespace Wego;

use App\Exceptions\FileNotFoundException;
use App\Exceptions\NotPermittedException;
use App\Exceptions\NullException;
use App\Http\Controllers\ProductPictureController;
use App\Http\Controllers\StorePictureController;
use App\Http\Requests;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wego\UserHandle\UserPermission;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic;

class PictureHandler
{
    const DELETE_UNSUCCESSFUL = 0;
    protected $path,$request,$userName,$resizeOption=[],$type;
    protected $destinationDirName;
    protected $destinationPath;
    protected $ProductPreFileName   = [ProductPictureController::PRODUCT_SIZE_OPTION1,ProductPictureController::PRODUCT_SIZE_OPTION2,'original'];
    protected $StorePreFileName     = [StorePictureController::THUMBNAIL_RESIZE,StorePictureController::INSIDE_RESIZE,'original'];
    protected $BuyerPreFileName     = ['original'];
    protected $StaffPreFileName     = ['original'];

    /**
     *defines and call two type of constructors depend on number of argument passed
     *
     */
    function __construct()
    {
        $argv = func_get_args();
        switch( func_num_args() ) {
            case 0:
                self::__construct1();
                break;
            case 2:
                self::__construct2( $argv[0], $argv[1] );
                break;
        }


    }
    function __construct1(){

    }
    function __construct2($request,$userName){
        $this->type = null;
        $this->request = $request;
        $this->userName = $userName;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }
    /**
     * save the picture in temporary path and returns the path
     *
     * @return string
     */
    public function save()
    {
        $file = $this->request->file('pic');
        $name = $this->createFileName($file);
        $file->move(public_path().'/'.$this->path,'/original_'.$name);
        $savePath=$this->createSavePath($name);
        if ($this->pictureHasSizeParameters())
            $img=$this->saveCroppedPictureWithGivenSize($savePath);
        else
            $img = $this->savePictureInOriginalSize($savePath);
        $this->createPicturesWithResizeOptions($img,$name);
        return $savePath;
    }

    /**
     * @param $img
     * @param $name
     */
    private function createPicturesWithResizeOptions($img,$name){
        foreach ($this->resizeOption as $size) {
            $img->resize($size,$size)->save(public_path().'/'.$this->path.'/'.$size.'_'.$name);
        }
    }

    /**
     * @return mixed
     */
    private function pictureHasSizeParameters(){
        return ($this->request->has('width'));
    }

    /**
     * @param $savePath
     * @return mixed
     */
    private function saveCroppedPictureWithGivenSize($savePath){
        return Image::make(public_path().$savePath)
            ->crop($this->request->input('width'), $this->request->input('height'),
                $this->request->input('x'), $this->request->input('y'))
            ->resizeCanvas($this->request->input('width'), $this->request->input('height'))
            ->save(public_path().$savePath);
    }

    /**
     * @param $savePath
     * @return mixed
     */
    private function savePictureInOriginalSize($savePath){
        return Image::make(public_path().$savePath)
            ->save(public_path().$savePath);
    }


    /**
     * @param $file
     * @return string
     */
    private function createFileName($file){
        return time().'.'.$file->getClientOriginalExtension();
    }

    /**
     * @param $name
     * @return string
     */
    private function createSavePath($name){
        return ('/'.$this->path.'/original_'.$name);
    }
    /**
     * saves the picture in requested dimensions
     *
     * @param $width
     * @param $height
     * @return string
     */
    public function saveRectangle($width, $height)
    {
        $file = $this->request->file('pic');
        $name = $this->createFileName($file);
        $file->move(public_path().'/'.$this->path,'/original_'.$name);
        $savePath=$this->createSavePath($name);
        $this->saveCroppedPictureInRectangle($savePath,$width,$height);
        return $savePath;
    }

    /**
     * @param $savePath
     * @param $width
     * @param $height
     */
    private function saveCroppedPictureInRectangle($savePath,$width,$height){
        Image::make(public_path().$savePath)
            ->crop($this->request->input('width'),$this->request->input('height'),
                $this->request->input('x'),$this->request->input('y'))
            ->resize($width,$height)
            ->save(public_path().$savePath);
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param array $resize
     * @return $this
     */
    public function setResizeOption($resize = [])
    {
        $this->resizeOption = $resize;
        return $this;
    }

    /**
     * @return $this
     */
    private function setType()
    {
        if($this->request->has('type'))
            $this->type = '/'.$this->request->input('type');
        return $this;
    }

    /**
     * set the temporary path of the picture depend on owner of the picture
     *
     * @param $using
     * @return $this
     */
    public function setUsePath($using)
    {
        $this->setType();
        if(strcmp($using,"product") === 0)
            $this->setPath('wego/store/store'.$this->userName.'/product/temp'.$this->type);
        if(strcmp($using,"store") === 0)
            $this->setPath('wego/staff/staff'.$this->userName.'/temp'.$this->type);
        if (strcmp($using,"buyer") === 0)
            $this->setPath('wego/buyer/buyer'.$this->userName.'/temp');
        if (strcmp($using,'banner') == 0)
            $this->setPath('wego/banner');
        return $this;
    }

    /**
     * @param $pictures
     */
    private function checkPicturesCount($pictures){
        if(count($pictures) > 5)
            throw new AccessDeniedHttpException;
    }

    /**
     * stores the picture in permanent path and insert the information in the database
     *
     * @param $picture
     * @param $owner
     * @param $user
     */
    public function storePicture($picture,$owner,$user=null)
    {
        $user=$this->setUserIfNull($user,$owner);
        $this->checkPicturesCount($picture);
        $type=$this->getPictureOwnerType($owner);
        $className=$this->getModelName($type);
        $this->storeAllRequestedPictures($className,$owner,$user,$picture,$type);
    }

    private function setUserIfNull($user,$owner){
        $user = isset($user) ? $user : $owner->user;
        return $user;
    }

    /**
     * gets the requested pictures and move temp pictures and save them to database
     *
     * @param $className
     * @param $owner
     * @param $user
     * @param $picture
     * @param $type
     */
    private function storeAllRequestedPictures($className,$owner,$user,$picture,$type){
        foreach ($picture as $item) {
            $currentPicturePath = $this->moveAllPictures(ltrim($item["path"],'/'),$owner,$type);
            $this->updatePicturesInDB($className,$item,$currentPicturePath,$owner,$user);
        }
    }

    /**
     * @param $className
     * @param $item
     * @param $currentPicturePath
     * @param $owner
     * @param $user
     * @internal param $type
     */
    private function updatePicturesInDB($className,$item,$currentPicturePath,$owner,$user){
        if ($this->isUserStaffOrStore($user))
            $this->updateStoreOrProductPicturesInDB($className,$item['type'],$currentPicturePath,$owner);
        else
            $this->updateBuyerPictureInDB($owner,$currentPicturePath);
    }

    /**
     * @param $modelName
     * @param $type
     * @param $path
     * @param $owner
     * @internal param $pictures
     */
    private function UpdateStoreOrProductPicturesInDB($modelName,$type,$path,$owner){
        $picture= (new $modelName([
            'type' => $type,
            'path' => ($path)
        ]));
        $owner->pictures()->save($picture);
    }

    /**
     * @param $buyer
     * @param $path
     */
    private function updateBuyerPictureInDB($buyer,$path){
        $buyer->update(['image_path'=>$path]);
    }

    /**
     * @param $user
     * @return bool
     */
    private function isUserStaffOrStore($user){
        return ($user->userable_type==UserPermission::STAFF or $user->userable_type==UserPermission::STORE);
    }

    /**
     * @param $tempFilePth
     * @param $picOwner
     * @return mixed
     */
    private function buildDestinationPath($tempFilePth,$picOwner){
        $destinationPath=$tempFilePth;
        $destinationPath=(preg_replace('/(.?wego.staff.staff([0-9]+).temp.)(.*)/',
            '/wego/store/store'.$picOwner->id.'/storePicture'.'/'.'$3',$destinationPath));
        $destinationPath=(preg_replace('/(.?wego.buyer.buyer([0-9]+).temp.)(.*)/',
            '/wego/buyer/buyer'.$picOwner->id.'/'.'$3',$destinationPath));
        $destinationPath=(preg_replace('/(.?wego.store.store([0-9]+).product.temp.)(.*)/',
            '/wego/store/store$2/product/product'.$picOwner->id.'/$3',$destinationPath));
        $destinationPath=(preg_replace('/(.?wego.staff.staff.sitePics.temp.)(.*)/',
            '/wego/staff/staff'.$picOwner->id.'/sitePics'.'/'.'$2',$destinationPath));
        return $destinationPath;
    }

    /**
     * @param $sourcePath
     * @param $destinationPath
     */
    private function movePicture($sourcePath,$destinationPath){
        if(file_exists(public_path().'/'.$sourcePath)){
            rename(public_path().'/'.$sourcePath , public_path().'/' . $destinationPath);
        }
    }
    /**
     * move all related picture from temporary path to permanent path
     *
     * @param $originalPath
     * @param $picOwner
     * @param $type
     * @return mixed
     */
    public function moveAllPictures($originalPath,$picOwner,$type){
        $destinationPath=$this->buildDestinationPath($originalPath,$picOwner);
        $this->makeDirIfNotExists(dirname(dirname('/'.$destinationPath)));
        $this->makeDirIfNotExists(dirname('/'.$destinationPath));
        foreach ($this->{$type."PreFileName"} as $preFileName) {
            $filePath = str_replace('original',$preFileName,$originalPath);
            $destinationFilePath=str_replace('original',$preFileName,$destinationPath);
            $this->movePicture($filePath,$destinationFilePath);
        }
        return $destinationPath;
    }

    /**
     * make the given direction for picture if it does not exists
     *
     * @param $dirName
     */
    private function makeDirIfNotExists($dirName){
        $dirName = public_path().$dirName;
        if(!file_exists($dirName))
            mkdir($dirName,0755,true);
    }

    /**
     * replace the new pictures with old pictures
     *
     * @param $newPicture
     * @param $oldPicture
     * @param $owner
     */
    public function changePicture($newPicture,$oldPicture,$owner){
        foreach($oldPicture as $oldPath){
            $this->deletePicture($oldPath['path'],$owner);
        }
        $user=$this->setTheUser($owner);
        $this->storePicture($newPicture,$owner,$user);

    }
    private function setTheUser($owner){
        $type=$this->getPictureOwnerType($owner);
        if (strcmp($type,'Product')==0)
            return ($owner->store->user);
        else
            return $owner->user;
    }

    /**
     * delete the picture permanently and remove it from database
     *
     * @param $path
     * @param $owner
     */
    public function deletePicture($path,$owner){
        $this->deletePicturesFromDB($owner,$path);
        $this->deletePicturesFromFile($owner,$path);
    }

    /**
     * delete the temporary saved picture
     *
     * @param $path
     * @return mixed
     */
    public function deleteTempPicture($path){
        $this->checkTempPicture($path);
        $this->deleteFile($path);
    }

    /**
     * @param $owner
     * @return mixed
     */
    private function getPictureOwnerType($owner)
    {
        return str_replace("App\\","",get_class($owner));
    }

    /**
     * @param $picOwnerType
     * @return string
     */
    private function getModelName($picOwnerType)
    {
        return "App\\".$picOwnerType . 'Picture';
    }

    /**
     * @param $owner
     * @param $path
     */
    private function deleteStoreOrProductPicturesFromDB($owner,$path)
    {
        $picOwnerType   = $this->getPictureOwnerType($owner);
        $modelName      = $this->getModelName($picOwnerType);
        $searchField    = strtolower($picOwnerType).'_id';
        $deleteStatus   = $modelName::where('path', $path)
                                        ->where($searchField,$owner->id)
                                        ->delete();

        if($deleteStatus === self::DELETE_UNSUCCESSFUL)
            throw new NotFoundHttpException('not exist in the database');
    }

    /**
     * @param $owner
     * @return bool
     */
    private function isOwnerStoreOrProduct($owner)
    {
        $picOwnerType = $this->getPictureOwnerType($owner);
        return (strcmp('Store',$picOwnerType) == 0 or strcmp('Product',$picOwnerType) == 0);
    }

    /**
     * @param $owner
     * @param $path
     * @return mixed
     */
    private function deleteBuyerPictureFromDB($owner,$path)
    {
        $picOwnerType   = $this->getPictureOwnerType($owner);
        $modelName      = "App\\".$picOwnerType;
        return $modelName::where('image_path', '=', $path)
            ->where('id','=',$owner->id)
            ->update(['image_path' => null]);
    }

    /**
     * @param $owner
     * @param $path
     */
    private function deletePicturesFromDB($owner,$path)
    {
        if($this->isOwnerStoreOrProduct($owner)) {
            $this->deleteStoreOrProductPicturesFromDB($owner,$path);
        }
        else { // owner is buyer
            $this->deleteBuyerPictureFromDB($owner,$path);
        }
        //throw new FileNotFoundException;
    }

    /**
     * @param $owner
     * @param $path
     */
    public function deletePicturesFromFile($owner, $path)
    {
        $picOwnerType = $this->getPictureOwnerType($owner);
       // $this->checkIfFileExists($path);
        foreach ($this->{$picOwnerType . "PreFileName"} as $preFileName) {
            $filePath = str_replace('original', $preFileName, $path);
            $this->deleteFile($filePath);
        }
    }

    private function checkIfFileExists($path){
        if (! file_exists(public_path() . $path)) {
            throw new NotFoundHttpException('file peyda nashod');
        }
    }

    /**
     * @param $path
     */
    private function deleteFile($path)
    {
        if (file_exists(public_path() . $path)) {
            File::delete(public_path() . $path);
        }
    }

    /**
     * @param $path
     */
    private function checkTempPicture($path)
    {
        if(strpos($path,'temp') === false)
            throw new FileNotFoundException('file temp nabud');
    }
}