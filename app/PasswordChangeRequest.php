<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PasswordChangeRequest extends Model
{
    protected $fillable = ['token','email','date'];
    public $timestamps = false;
    const VALIDITY_DURATION = 1;
    protected $primaryKey = 'token';
    public function isOld(){
        if(Carbon::now()->diffInDays(Carbon::parse($this->date)) < self::VALIDITY_DURATION)
            return false;
        return true;
    }
}
