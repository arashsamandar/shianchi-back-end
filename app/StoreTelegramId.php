<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreTelegramId extends Model
{
    protected $fillable = ['store_id','telegram_username'];
}
