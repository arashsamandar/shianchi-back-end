<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductOrder extends Model
{
    public function address()
    {
        return $this->belongsTo(BuyerAddress::class);
    }
}
