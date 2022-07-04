<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    protected $fillable = [
        'last_name','mobile_number','landline_number',
        'address','card_number','card_owner_name','image_path',
        'gender','magazine_subscriber', 'national_code',
        'company_name','comment_score','birthday'
    ];
    protected $hidden = ['id'];

    public function user()
    {
        return $this->morphOne(User::class,'userable');
    }
}

