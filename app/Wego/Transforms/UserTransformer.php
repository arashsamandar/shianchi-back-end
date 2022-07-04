<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 2/19/17
 * Time: 10:50 AM
 */

namespace Wego\Transforms;

use App\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{

    protected $availableIncludes = [
        'addresses',
        'wegoCoin'
    ];

    /**
     * Turn this item object into a generic array
     *
     * @param User $user
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'name' => $user->name,
            'u_id' => $user->userable_id,
            'email' => $user->email,
        ];
    }

    /**
     * Include Author
     *
     * @param User $user
     * @return League\Fractal\ItemResource
     */
    public function includeAddresses(User $user)
    {
        $addresses = $user->addresses;

        return $this->collection($addresses, new AddressTransformer);
    }
    public function includeWegoCoin(User $user){

        $wegoCoin = $user->wegoCoin;

        return $this->collection($wegoCoin, new WegoCoinTransformer);
    }

}