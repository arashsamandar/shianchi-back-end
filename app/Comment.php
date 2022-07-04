<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Wego\ShamsiCalender\Shamsi;

class Comment extends Model
{
    const REJECTED = 0;
    const IN_PROGRESS = 1;
    const CONFIRMED = 2;
    protected $fillable = ['status'];
    public function user()
    {
        return $this->belongsToMany(User::class,'comments','product_id','user_id');
    }
}
