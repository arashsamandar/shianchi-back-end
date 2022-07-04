<?php

namespace App\Repositories\Category;

use App\Category;
use App\Repositories\Eloquent\WegoBaseRepository;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Category\CategoryRepository;


/**
 * Class CategoryRepositoryEloquent
 * @package namespace App\Repositories;
 */
class CategoryRepositoryEloquent extends WegoBaseRepository implements CategoryRepository
{
    protected $fieldSearchable = [
        'id',
        'persian_name',
        'specifications.id'
    ];
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Category::class;
    }

    public function getTitles($id)
    {
        return $this->model->find($id)->titles;
    }

    //todo any better way???
    public function detachNotMentionedTitles($id, $titleIds)
    {
        $notMentioned = $this->model->find($id)->titles()->whereNotIn('id', $titleIds)->get();
        foreach ($notMentioned as $nm) {
            $this->model->find($id)->titles()->detach($nm->id);
        }
    }

    public function getAllCategoriesTitles($categoryIds, $columns = ['*'])
    {
        return DB::table('category_title')->whereIn('category_id', $categoryIds)->select($columns)->get();

    }

    public function getBrands($categoryId)
    {
        return $this->model->find($categoryId)->brands;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
