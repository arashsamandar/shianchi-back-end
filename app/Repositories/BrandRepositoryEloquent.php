<?php

namespace App\Repositories;

use App\Brand;
use App\Repositories\Eloquent\WegoBaseRepository;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\BrandRepository;


/**
 * Class BrandRepositoryEloquent
 * @package namespace App\Repositories;
 */
class BrandRepositoryEloquent extends WegoBaseRepository implements BrandRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Brand::class;
    }

    public function getSimilarBrands($name)
    {
        $name = '%' . $name . '%';
        $model = $this->model->where('english_name', 'like', $name)->orWhere('persian_name', 'like', $name)->get();
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function attachCategories($brandId, $categoryIds)
    {
        $model = $this->model->find($brandId)->categories()->attach($categoryIds);
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
