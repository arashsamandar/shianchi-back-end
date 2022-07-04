<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Guarantee extends Model
{
    protected $fillable = ["type"];

    protected $hidden = ['created_at','updated_at'];

}
