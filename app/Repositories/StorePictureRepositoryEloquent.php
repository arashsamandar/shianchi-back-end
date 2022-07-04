<?php

namespace App\Repositories;

use App\Repositories\Eloquent\WegoBaseRepository;
use App\StorePicture;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\StorePictureRepository;


/**
 * Class StorePictureRepositoryEloquent
 * @package namespace App\Repositories;
 */
class StorePictureRepositoryEloquent extends WegoBaseRepository implements StorePictureRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return StorePicture::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
