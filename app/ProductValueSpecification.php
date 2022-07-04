<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductValueSpecification extends Model
{
    protected $fillable = ['product_id','specification_id','value_id'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function specification()
    {
        return $this->belongsTo(Specification::class);
    }
    public function value()
    {
        return $this->belongsTo(Value::class);
    }
}
