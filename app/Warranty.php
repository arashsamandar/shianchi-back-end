<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warranty extends Model
{
    use SoftDeletes;
    protected $table = 'warranties';
    protected $fillable = ['warranty_name','warranty_text'];
    protected $hidden = ['created_at','updated_at','deleted_at'];

}
