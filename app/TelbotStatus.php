<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelbotStatus extends Model
{
    const STARTED = 1;
    const INSERTING_PRODUCT_NAME_FOR_QUANTITY= 2;
    const INSERTING_THE_ID_OF_PRODUCT_FOR_QUANTITY = 3;
    const INSERTING_THE_QUANTITY_OF_PRODUCT = 4;
    const UPDATE_THE_QUANTITY_OF_PRODUCT = 5;



    const INSERTING_PRODUCT_NAME_FOR_PRICE = 6;
    const INSERTING_THE_ID_OF_PRODUCT_FOR_PRICE = 7;
    const INSERTING_THE_PRICE_OF_PRODUCT = 8;
    const UPDATE_THE_PRICE_OF_PRODUCT = 9;
    protected $fillable = ['status','id'];
}
