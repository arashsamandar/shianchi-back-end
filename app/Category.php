<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Elasticquent\ElasticquentTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Wego\Helpers\JsonUtil;

class Category extends Model
{
    use ElasticquentTrait;
    use softDeletes;
    protected $fillable = ['persian_name' , 'unit' , 'isLeaf' , 'menu_id' , 'path' , 'english_path', 'name'];
    //protected $hidden = ['updated_at','created_at','lft','rgt'];
    protected $table = 'categories';

    public function specifications()
    {
        return $this->hasMany(Specification::class);
    }

    public function nontext_specifications()
    {
        return $this->hasMany(Specification::class)->where('is_text_field','<>',1);
    }

    public function text_specifications()
    {
        return $this->hasMany(Specification::class)->where('is_text_field',1);
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function titles()
    {
        return $this->belongsToMany(Title::class, 'category_title');
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'category_brand')->withTimestamps();
    }

    public function setPersianNameAttribute($value)
    {
        $this->attributes['persian_name'] = str_replace('ي','ی',$value);
    }

    public function scopeElastic($query)
    {
        $cat = $query->with(['nontext_specifications' => function ($query) {
            $query->with(['values']);
        }, 'text_specifications','brands' , 'colors'])->get();
        $cat->each(function($item){
            $item['specifications'] = array_merge($item['nontext_specifications']->toArray(),$item['text_specifications']->toArray());
        });
        return JsonUtil::removeFields($cat, ['*.nontext_specifications', '*.text_specifications']);

    }

}
