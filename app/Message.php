<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Wego\ShamsiCalender\Shamsi;

class Message extends Model
{
    protected $fillable = ['sender_id','receiver_id','type','body'];

    public function user()
    {
        return $this->belongsTo(User::class,'sender_id',null,'messages');
    }
    public function getCreatedAtAttribute($value){
        return Shamsi::convert(Carbon::parse($value));
    }
    public function getUpdatedAtAttribute($value){
        return Shamsi::convert(Carbon::parse($value));
    }
}
