<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = ['price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
