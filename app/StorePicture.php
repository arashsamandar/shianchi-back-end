<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StorePicture extends Model
{
    protected $fillable = ['type','path'];
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
