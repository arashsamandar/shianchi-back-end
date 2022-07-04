<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Specification extends Model
{
    protected $fillable = ['name', 'for_buy', 'multi_value', 'is_text_field', 'important', 'searchable', 'category_id', 'title_id'];
    const FOR_BUY = 1;
    const NOT_FOR_BUY = 0;
    const SEARCHABLE = 1;
    const NOT_SEARCHABLE = 0;
    const MULTI_VALUE = 1;
    const SINGLE_VALUE = 0;
    const SELECTABLE = 1;
    const FILLABLE = 0;
    const IMPORTANT = 1;
    const NOT_IMPORTANT = 0;

    //protected $hidden = ['updated_at','created_at'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function values()
    {
        return $this->hasMany(Value::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function buyProductSpecification()
    {
        return $this->hasMany(BuyProductSpecification::class);
    }

    public function title()
    {
        return $this->belongsTo(Title::class);
    }

    public function setMultiValueAttribute($value)
    {
        if ($this->attributes['is_text_field'] == 0) {
            $this->attributes['multi_value'] = $value;
        } else {
            $this->attributes['multi_value'] = 0;
        }
    }

    public function setForBuyAttribute($value)
    {
        if ($this->attributes['is_text_field'] == 0) {
            $this->attributes['for_buy'] = $value;
        } else {
            $this->attributes['for_buy'] = 0;
        }
    }

    public function setSearchableAttribute($value)
    {
        if ($this->attributes['is_text_field'] == 0) {
            $this->attributes['searchable'] = $value;
        } else {
            $this->attributes['searchable'] = 0;
        }
    }
    public static function isTextField(array $specification){
        return $specification['is_text_field'] == 1;
    }

    public static function isMultipleValue(array $specification){
        return $specification['multi_value'] == 1;
    }
}
