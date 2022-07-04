<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreOffer extends Model
{
    protected $fillable = ['order_product_id','store_price','store_id'];


    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
