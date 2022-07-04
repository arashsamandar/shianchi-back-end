<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    const PAID_STATUS='paid';
    const NOT_PAID_STATUS ='not_paid';
    const ORDER_TYPE='order_Audit';
    public $timestamps = false;
    protected $fillable = [
        'store_id','start_date','end_date','crude_price','final_amount','wego_rights','status','type'
    ];
    public function orders(){
        return $this->belongsToMany(Order::class,'order_audit');
    }
    //
}
