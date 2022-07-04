<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductPicture extends Model
{
    protected $fillable = ['path','type','product_id'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
