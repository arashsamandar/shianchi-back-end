<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    public function products()
    {
        return $this->belongsToMany(Product::class,'favorites','product_id');
    }

    public function buyer()
    {
        return $this->belongsToMany(Buyer::class);
    }
}
