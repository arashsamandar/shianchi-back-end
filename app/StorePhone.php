<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StorePhone extends Model
{
    protected $fillable = ['prefix_phone_number','phone_number','type'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
