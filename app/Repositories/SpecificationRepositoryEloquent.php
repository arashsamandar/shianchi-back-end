<?php

namespace App\Repositories;

use App\Repositories\Eloquent\WegoBaseRepository;
use App\Specification;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\SpecificationRepository;


/**
 * Class SpecificationRepositoryEloquent
 * @package namespace App\Repositories;
 */
class SpecificationRepositoryEloquent extends WegoBaseRepository implements SpecificationRepository
{
    protected $fieldSearchable = [
        'id'
    ];
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Specification::class;
    }

    public function getValues($id)
    {
        $model = $this->model->find($id)->values;
        $this->resetModel();
        return $this->parserResult($model);
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
