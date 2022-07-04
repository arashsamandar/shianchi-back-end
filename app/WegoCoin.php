<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Wego\ShamsiCalender\Shamsi;
use Carbon\Carbon;

class WegoCoin extends Model
{
    protected $fillable = ['user_id','store_id','amount','status','expiration'];
    const AVAILABLE = 'a';
    const EXPIRED = 'e';
    const VALUE_TOMAN = 1000;
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    
}
