<?php

namespace App\Repositories;

use App\Audit;
use App\Repositories\Eloquent\WegoBaseRepository;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\AuditRepository;


/**
 * Class AuditRepositoryEloquent
 * @package namespace App\Repositories;
 */
class AuditRepositoryEloquent extends WegoBaseRepository implements AuditRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Audit::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
