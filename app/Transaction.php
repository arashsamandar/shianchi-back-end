<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const IN_PROGRESS = 0;
    const SUCCESSFUL = 1;
    const FAILED_NORMAL = 2;
    const FAILED_SUSPICIOUS = 3;

    protected $fillable = [
        'amount','element_id','ip','status','service_type','bank_name','user_id','refNum','error_message',
    ];
}
