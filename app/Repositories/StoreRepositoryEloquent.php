<?php

namespace App\Repositories;

use App\Repositories\Eloquent\WegoBaseRepository;
use App\Store;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\StoreRepository;
use Wego\PictureHandler;


/**
 * Class StoreRepositoryEloquent
 * @package namespace App\Repositories;
 */
class StoreRepositoryEloquent extends WegoBaseRepository implements StoreRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Store::class;
    }

    public function save($all)
    {
        $store = $this->create($all);
        $user = $store->user()->create(
            [
                'email' => $all['email'] ,
                'password' => bcrypt($all['password']),
                'name' => $all['persian_name']
            ]);
        //$store->manager_picture = (preg_replace('/(.wego.staff.staff([0-9]+).temp.)(.*)/','/wego/store/store'.$store->id.'/storePicture'.'/'.'$3',$requests['manager_picture']));
        $store->url = strtolower(str_replace(' ','-',$store->english_name));
        $store->save();
        $store->bazaar()->attach($all['bazaar']);
        return $store;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
