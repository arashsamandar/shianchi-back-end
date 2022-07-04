<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Competitor extends Model
{
    public function products()
    {
        return $this->hasMany(Product::class,'id','competitors_id');
    }

    public function stores()
    {
        return $this->hasMany(Store::class,'id','competitors_id');
    }
}
