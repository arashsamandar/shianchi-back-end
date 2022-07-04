<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BazaarStaffMessage extends Model
{
    protected $fillable = ['product_id','order_id','message'];

    public function order(){
        return $this->belongsTo('App\Order');
    }

    public function product(){
        return $this->hasOne('App\Product');
    }
}
