<?php

namespace App\Policies;

use App\User;
use App\BuyerAddress;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the buyerAddress.
     *
     * @param  \App\User  $user
     * @param  \App\BuyerAddress  $buyerAddress
     * @return mixed
     */
    public function view(User $user, BuyerAddress $buyerAddress)
    {
        return $user->id == $buyerAddress->user_id;
    }

    /**
     * Determine whether the user can create buyerAddresses.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the buyerAddress.
     *
     * @param  \App\User  $user
     * @param  \App\BuyerAddress  $buyerAddress
     * @return mixed
     */
    public function update(User $user, BuyerAddress $buyerAddress)
    {
        //
    }

    /**
     * Determine whether the user can delete the buyerAddress.
     *
     * @param  \App\User  $user
     * @param  \App\BuyerAddress  $buyerAddress
     * @return mixed
     */
    public function delete(User $user, BuyerAddress $buyerAddress)
    {
        //
    }
}
