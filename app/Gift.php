<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    protected $fillable = ['id', 'amount', 'expiration_time','min_purchase'];
    public $incrementing = false;
    const AVAILABLE = 'available';
    const EXPIRED = 'expired';

    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }
}
