<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staffs';
    protected $fillable = ['last_name','rule','bazaar_id','mobile'];

    public function user(){
        return $this->morphOne('App\User','userable');
    }

    public function Bazaars(){
        return $this->belongsToMany('App\Bazaar');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}