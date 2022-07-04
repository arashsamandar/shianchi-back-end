<?php

namespace App\Repositories;

use App\Product;
use App\Repositories\Eloquent\WegoBaseRepository;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\ProductRepository;


/**
 * Class ProductRepositoryEloquent
 * @package namespace App\Repositories;
 */
class ProductRepositoryEloquent extends WegoBaseRepository implements ProductRepository
{

    protected $fieldSearchable = [
        'id',
        'name',
        'persian_name'
    ];
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Product::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
