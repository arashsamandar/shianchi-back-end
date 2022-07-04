<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutsideOrder extends Model
{
    protected $fillable = ['name' , 'phone_number' , 'link' , 'description'];

}
