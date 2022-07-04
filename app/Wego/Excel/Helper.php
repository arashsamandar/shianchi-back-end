<?php
use Intervention\Image\Facades\Image;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 26/12/16
 * Time: 19:15
 */
class Helper
{
//    function __construct(){
//        $path = $this->getInputFilePath();
//
//        $this->objPHPExcel = PHPExcel_IOFactory::load($path);
//    }
    public static function maxWidthHeightInDirectory($path)
    {
        $axs = scandir($path);
        unset($axs[0]);
        unset($axs[1]);
        $w = 0;
        $h = 0;
        foreach ($axs as $ax) {
            $pic = Image::make($path.trim($ax));
            $w = max($pic->width(),$w);
            $h = max($pic->height(),$h);
        }
        return ['width'=>$w,'height'=>$h];
    }

//    public static function chunkExcel(){
//        $objWriter = new PHPExcel_Writer_Excel2007($this->objPHPExcel);
//        $objWriter->save($this->getOutputFilePath());
//    }

}