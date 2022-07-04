<?php
namespace Wego\Picture;

use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 15/03/17
 * Time: 16:17
 */
class Picture
{
    protected $picture;
    protected $tempPath;

    function __construct()
    {

        $this->tempPath = public_path() . '/temp';
    }

    public function saveTemp($x, $y, $height, $width)
    {
        $this->move();
        Image::make($this->picture)
            ->crop($width, $height, $x, $y)
                ->resize($width, $height)
            ->save($this->tempPath);
        return $this->getName();
    }

    public function move()
    {
        $this->picture->move($this->getName());
    }

    private function getName()
    {
        $time = microtime();
        return $this->tempPath . '/' . $time . $this->picture->getClientOriginalExtension();
    }

    /**
     * @param mixed $picture
     * @return Picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
        return $this;
    }

}