<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $fillable = ['id' ,'image_path','link','persian_name'];
    public $incrementing = false;

}
