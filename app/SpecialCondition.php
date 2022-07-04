<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SpecialCondition extends Model
{
    const NO_EXPIRATION = 10 * 365 ; // 100 YEARS
    const AVAILABLE = 'a';
    const EXPIRED = 'e';
    protected $fillable = ["type","amount","expiration","text","upper_value","upper_value_type","product_detail_id"];
    public static $specialTypes = ["gift","discount","wego_coin"];
    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class);
    }
    public function setExpirationAttribute($value)
    {
        $this->attributes['expiration'] = Carbon::now()->addDay($value);
    }
}
