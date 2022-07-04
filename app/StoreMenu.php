<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreMenu extends Model
{
    protected $fillable = ['title','body'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
