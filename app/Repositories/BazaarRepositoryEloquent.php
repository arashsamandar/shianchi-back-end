<?php

namespace App\Repositories;

use App\Bazaar;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\BazaarRepository;


/**
 * Class BazaarRepositoryEloquent
 * @package namespace App\Repositories;
 */
class BazaarRepositoryEloquent extends BaseRepository implements BazaarRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Bazaar::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
