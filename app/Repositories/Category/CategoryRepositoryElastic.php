<?php

namespace App\Repositories\Category;

use App\Category;
use App\Repositories\Elastic\ElasticRepository;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Interface CategoryRepository
 * @package namespace App\Repositories;
 */
class CategoryRepositoryElastic extends ElasticRepository implements CategoryRepository
{
    protected $fieldSearchable = [
        'name',
        'id'
    ];
    public function model()
    {
        return Category::class;
    }
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function insert($data)
    {
        // TODO: Implement insert() method.
    }

    public function take($count)
    {
        // TODO: Implement take() method.
    }

    public function deleteWhereIn($column, array $values)
    {
        // TODO: Implement deleteWhereIn() method.
    }
}