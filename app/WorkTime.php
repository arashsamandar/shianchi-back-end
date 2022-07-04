<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class WorkTime extends Model
{
    protected $fillable = ['day',"opening_time","closing_time",'is_closed'];
    public function work_time()
    {
        return $this->belongsTo(Store::class);
    }
}
