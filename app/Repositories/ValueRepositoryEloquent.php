<?php

namespace App\Repositories;

use App\Repositories\Eloquent\WegoBaseRepository;
use App\Value;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\ValueRepository;


/**
 * Class ValueRepositoryEloquent
 * @package namespace App\Repositories;
 */
class ValueRepositoryEloquent extends WegoBaseRepository implements ValueRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Value::class;
    }

    public function deleteNotMentionedSpecificationValues($specificationId, $valueIds)
    {
        $model = $this->model->where('specification_id', '=', $specificationId)->whereNotIn('id', $valueIds)->delete();
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
