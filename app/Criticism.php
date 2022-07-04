<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Wego\ShamsiCalender\Shamsi;

class Criticism extends Model
{
    protected $fillable = ['type','body'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getCreatedAtAttribute($value){
        return Shamsi::convert(Carbon::parse($value));
    }
    public function getUpdatedAtAttribute($value){
        return Shamsi::convert(Carbon::parse($value));
    }
}
