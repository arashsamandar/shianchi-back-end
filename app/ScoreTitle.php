<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScoreTitle extends Model
{
    protected $table ='score_title';
    protected $hidden = ['created_at','updated_at'];
}
