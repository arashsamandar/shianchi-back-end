<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorldcupGame extends Model
{
    protected $fillable = ['id',"game_time","result"];
    public $incrementing = false; // <---------
    protected $hidden = [
        'created_at','updated_at'
    ];

    public function prediction()
    {
        return $this->hasMany(WorldcupPredictions::class);
    }

}
