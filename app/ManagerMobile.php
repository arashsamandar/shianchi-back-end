<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ManagerMobile extends Model
{
    protected $fillable = ['prefix_phone_number','phone_number'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
