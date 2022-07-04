<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Title extends Model
{
    protected $fillable = ['title', 'category_id'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_title')->withTimestamps();
    }

    public function specifications()
    {
        return $this->hasMany(Specification::class);
    }
}
