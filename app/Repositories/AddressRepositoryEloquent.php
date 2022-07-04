<?php

namespace App\Repositories;

use App\BuyerAddress;
use App\Repositories\Eloquent\WegoBaseRepository;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\AddressRepository;


/**
 * Class AddressRepositoryEloquent
 * @package namespace App\Repositories;
 */
class AddressRepositoryEloquent extends WegoBaseRepository implements AddressRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return BuyerAddress::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
