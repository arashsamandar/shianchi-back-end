<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RejectionMessage extends Model
{
    protected $fillable = ['message','is_read'];

    public function product(){
        return $this->belongsTo('App\Product');
    }
}
