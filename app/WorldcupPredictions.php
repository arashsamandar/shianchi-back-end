<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorldcupPredictions extends Model
{
    protected $fillable = ['ip',"game_id","user_id","prediction","mobile_number"];
    protected $hidden = [
        'created_at','updated_at'
    ];
    public function game()
    {
        return $this->belongsTo(WorldcupGame::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
