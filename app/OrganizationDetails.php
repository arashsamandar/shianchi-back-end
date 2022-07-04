<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrganizationDetails extends Model
{
    protected $fillable = ['company_name' , 'economic_code' , 'postal_code' , 'address' , 'order_id' , 'phone_number'];

}
