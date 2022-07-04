<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Wego\Province\Util\MemoryProvinceManager;
use Wego\Shipping\Company\AbstractShipping;

class BuyerAddress extends Model
{
    use softDeletes;
    protected $fillable = [
        'address','city_id','province_id','postal_code','phone_number',
        'prefix_phone_number','mobile_number',
        'prefix_mobile_number','receiver_first_name','receiver_last_name','national_code'
    ];
    protected $hidden =['created_at','updated_at','user_id'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function order()
    {
        return $this->belongsToMany(Order::class);
    }

    public function hasWegoShipping()
    {
        return ($this->city_id == MemoryProvinceManager::REY_CITY_ID || $this->city_id == MemoryProvinceManager::TEHRAN_CITY_ID);
    }
}
