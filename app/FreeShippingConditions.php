<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FreeShippingConditions extends Model
{
    protected $fillable = ["upper_value","type","city"];
}
