<?php

namespace App\Repositories;

use App\ProductPicture;
use App\Repositories\Eloquent\WegoBaseRepository;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\ProductPictureRepository;


/**
 * Class ProductPictureRepositoryEloquent
 * @package namespace App\Repositories;
 */
class ProductPictureRepositoryEloquent extends WegoBaseRepository implements ProductPictureRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductPicture::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
