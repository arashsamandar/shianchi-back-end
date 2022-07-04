<?php

namespace App\Repositories;

use App\Competitor;
use App\Repositories\Eloquent\WegoBaseRepository;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\FavoriteRepository;

/**
 * Class FavoriteRepositoryEloquent
 * @package namespace App\Repositories;
 */
class FavoriteRepositoryEloquent extends WegoBaseRepository implements FavoriteRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Competitor::class;
    }

    public function getStoreFavoriteByUser($userId)
    {
        $model = $this->model->where('competitors_type', '=', 'App\Store')
            ->where('user_id', '=', $userId)->with(['stores' => function ($query) {
                $query->with(['pictures' => function ($query) {
                    $query->where('type', '=', 'logo')->select('path', 'store_id');
                }, 'store_phones' => function ($query) {
                    $query->select('prefix_phone_number', 'phone_number', 'store_id');
                }, 'user' => function ($query) {
                    $query->select('userable_id', 'name');
                }])->select('english_name', 'information', 'id');
            }])->select('id', 'competitors_id')->get()->toArray();
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function getProductFavoriteByUser($userId)
    {
        $model = $this->model->where('competitors_type', '=', 'App\Product')
            ->where('user_id', '=', $userId)
            ->with(['products' => function ($query) {
                $query->with(['store' => function ($query) {
                    $query->with(['pictures' => function ($query) {
                        $query->where('type', '=', 'logo')->select('path', 'store_id');
                    }, 'store_phones' => function ($query) {
                        $query->select('store_id', 'prefix_phone_number', 'phone_number');
                    }, 'user' => function ($query) {
                        $query->select('name', 'userable_id');
                    }])->select('id', 'english_name', 'information');
                }, 'pictures' => function ($query) {
                    $query->where('type', '=', 0)->select('path', 'product_id');
                }])->select('store_id', 'id', 'english_name', 'persian_name');
            }])->select('user_id', 'competitors_id', 'id')->get()->toArray();
        $this->resetModel();
        return $this->parserResult($model);
    }

    public function userAddedThisStoreBefore($userId, $competitorId)
    {
        $count = $this->model->where('user_id', '=', $userId)
            ->where('competitors_type', '=', 'App\Store')
            ->where('competitors_id', '=', $competitorId)->count();
        $this->resetModel();
        return ($count > 0);
    }

    public function userAddedThisProductBefore($userId, $competitorId)
    {
        $count = Competitor::where('user_id', '=', $userId)
            ->where('competitors_type', '=', 'App\Product')
            ->where('competitors_id', '=', $competitorId)->count();
        $this->resetModel();
        return ($count > 0);
    }

    public function deleteFromFavorite($userId, $id)
    {
        //$this->deleteWhere();
        $model=$this->model->where('id', '=', $id)->where('user_id', '=', $userId)->delete();
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
