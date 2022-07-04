<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseOrder extends Model
{
    const PAYED = 1;
    const NOT_PAYED = 0;
    protected $fillable =['total_price','participant_id','total_discount'];
    protected $table = "course_order";

    public static function courseOrderIsPayed($orderId)
    {
        CourseOrder::where($orderId)->update(['status'=>self::PAYED]);
    }
}
