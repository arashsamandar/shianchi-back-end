<?php

namespace App\Repositories;

use App\Repositories\Eloquent\WegoBaseRepository;
use App\User;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\UserRepository;

/**
 * Class UserRepositoryEloquent
 * @package namespace App\Repositories;
 */
class UserRepositoryEloquent extends WegoBaseRepository implements UserRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return User::class;
    }

    public function saveAddresses($userId, $data)
    {
        $model = $this->model->find($userId)->addresses()->create($data);
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function updateAddresses($userId, $addressId, $data)
    {
        $model = $this->model->find($userId)->addresses->where('id', $addressId)->first()->update($data);
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function deleteAddresses($userId, $addressId)
    {
        $deleted = $this->model->find($userId)->addresses->where('id',$addressId)->first()->delete();
        $this->resetModel();
        return $deleted;
    }


    public function attachStoreToUser($userId, $storeId)
    {
        $model = $this->model->find($userId)->stores()->attach($storeId);
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function attachProductToUser($userId, $productId)
    {
        $model = $this->model->find($userId)->products()->attach($productId);
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function getAddresses($userId)
    {
        $model = $this->model->find($userId)->addresses;
        $this->resetModel();
        return $model;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
