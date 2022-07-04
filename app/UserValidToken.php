<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserValidToken extends Model
{
    protected $fillable = ['user_email','token'];
}
