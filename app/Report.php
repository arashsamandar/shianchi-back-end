<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'id','body','type','sender_id','reported_store_id','reported_product_id'];
    /**
     * each reports belongs to a user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sender(){
        return $this->belongsTo('App\User');
    }
    public function reportedStore(){
        return $this->belongsTo('App\Store');
    }
    public function reportedProduct(){
        return $this->belongsTo('App\Product');
    }

}