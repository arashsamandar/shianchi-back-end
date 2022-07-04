<?php

namespace App\Repositories;

use App\Repositories\Eloquent\WegoBaseRepository;
use App\Title;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\TitleRepository;


/**
 * Class TitleRepositoryEloquent
 * @package namespace App\Repositories;
 */
class TitleRepositoryEloquent extends WegoBaseRepository implements TitleRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Title::class;
    }

    public function attachCategories($id, $categoryIds)
    {
        $model = $this->model->find($id)->categories()->attach($categoryIds);
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
