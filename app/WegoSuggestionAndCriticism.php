<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WegoSuggestionAndCriticism extends Model
{
    protected $fillable = ['type','body'];
    protected $hidden = ['user_id','created_at','updated_at'];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
