<?php

namespace App;

use Dingo\Api\Exception\ValidationHttpException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mockery\CountValidator\Exception;


class ProductDetail extends Model
{
    use softDeletes;
    protected $fillable = [
        'product_id', 'store_id', 'warranty_id',
        'value_id', 'color_id', 'current_price',
        'quantity','uid','updated_at'
    ];
    protected $hidden = [
        'uid','deleted_at'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product', 'detail_id', 'order_id');
    }

    public function special_conditions()
    {
        return $this->hasMany(SpecialCondition::class);
    }

    public function scopeOrderDetail($query, $detailIds)
    {
        return $query->whereIn('id', $detailIds)->with(['product' => function ($query) {
            $query->select('weight', 'id');
        }, 'special_conditions' => function ($query) {
            $query->where('status', SpecialCondition::AVAILABLE)->select('id', 'type', 'upper_value', 'amount', 'text', 'product_detail_id');
        }])->select('product_id', 'current_price', 'id', 'quantity as existing_quantity');
    }

    public function ScopeReduceQuantity($query, $detailId, $reduceAmount)
    {
        $detail = $query->where('id', '=', $detailId)->first();
        $detail->quantity -= $reduceAmount;
        $detail->save();
        Product::where('id', $detail->product_id)->elastic()->addToIndex();
    }

    public function ScopeIncreaseQuantity($query, $detailId, $reduceAmount)
    {
        $detail = $query->where('id', '=', $detailId)->first();
        $detail->quantity += $reduceAmount;
        $detail->save();
        Product::where('id', $detail->product_id)->elastic()->addToIndex();
    }

    public function r_collect($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->r_collect($value);
                $array[$key] = $value;
            }
        }

        return collect($array);
    }

    public function scopeMergeQuantity($query, $quantities)
    {

        return $this->r_collect($query->get()->toArray())->map(function ($detail) use ($quantities) {
            return $detail->merge($quantities[array_search($detail->get('id'), array_column($quantities, 'detail_id'))]);
        });
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = $value;
    }

    public function color()
    {
        return $this->belongsTo(Color::class)->withTrashed();
    }

    public function value()
    {
        return $this->belongsTo(Value::class);
    }

    public function warranty()
    {
        return $this->belongsTo(Warranty::class)->withTrashed();
    }
}
