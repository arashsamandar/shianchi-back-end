<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameScore extends Model
{
    protected $fillable = ['name','email','telegram_username','call_number' , 'score' , 'contact_string'];
}
