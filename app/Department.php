<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Department extends Model
{

    protected $fillable = ['department_name'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function store(){
        return $this->belongsToMany(Store::class);
    }

    public static function getAllDepartments(){
        return Department::all(['id','department_name']);
    }
}
