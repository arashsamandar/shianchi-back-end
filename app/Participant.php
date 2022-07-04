<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    public static  $type = ['open', 'student', 'sbu_student'];
    public static  $discountPerType = ['open' => 0, 'student' => .5, 'sbu_student' => .75];
    protected $fillable =['name','email','phone_number','type'];
    public function courses()
    {
        return $this->belongsToMany(Course::class)->select('id')->withTimestamps();
    }
}
