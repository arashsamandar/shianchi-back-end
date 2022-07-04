<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Value extends Model
{
    protected $fillable = ['name','specification_id'];
    //protected $hidden = ['created_at','updated_at'];
    public function specification()
    {
        return $this->belongsTo(Specification::class);
    }

}
