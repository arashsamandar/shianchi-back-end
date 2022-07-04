<?php

namespace App;

use Bican\Roles\Traits\HasRoleAndPermission;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Wego\UserHandle\UserPermission;
use Bican\Roles\Contracts\HasRoleAndPermission as HasRoleAndPermissionContract;
use Zizaco\Entrust\Traits\EntrustUserTrait;


class User extends Authenticatable
{
    use EntrustUserTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'name'
    ];


    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','created_at','updated_at'
    ];
    public function reports(){
        return $this->hasMany('App\Report','sender_id');
    }

    public function products()
    {
        return $this->morphedByMany('App\Product','competitors')->withTimestamps();
    }
    public function stores()
    {
        return $this->morphedByMany('App\Store','competitors')->withTimestamps();
    }
    public function userable()
    {
        return $this->morphTo();
    }

    public function order()
    {
        return $this->hasMany(Order::class);
    }

    //TODO : too in halat staff o store wegoCoin mitunan dashte bashan
    public function wegoCoin()
    {
        return $this->hasMany(WegoCoin::class);
    }

    public function addresses()
    {
        return $this->hasMany(BuyerAddress::class);
    }

    public function receive_message()
    {
        return $this->belongsToMany(Message::class,'messages','sender_id','receiver_id');
    }

    public function comments()
    {
        return $this->belongsToMany(User::class,'comments','product_id','user_id')->withPivot('body');
    }

    public function criticism()
    {
        return $this->hasMany(Criticism::class);
    }

    public function wego_suggestion_and_criticism(){
        return $this->hasMany(WegoSuggestionAndCriticism::class);
    }

    public function target_products(){
        return $this->belongsToMany(Product::class,'stalker_user');
    }

    public function getUserableType(){
        $user = $this;
        $userPermission = new UserPermission();
        $userPermission->setUser($user);
        $result = null;
        if($userPermission->isBuyer()){
            $result = Buyer::find($user->userable_id);
        }elseif($userPermission->isStaff()){
            $result = Staff::find($user->userable_id);
        }elseif($userPermission->isStore()){
            $result = Store::find($user->userable_id);
        }
        return $result;
    }
}
