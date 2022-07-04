<?php

namespace App\Repositories;

use App\Comment;
use App\Http\Controllers\CommentController;
use App\Repositories\Eloquent\WegoBaseRepository;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\CommentRepository;


/**
 * Class CommentRepositoryEloquent
 * @package namespace App\Repositories;
 */
class CommentRepositoryEloquent extends WegoBaseRepository implements CommentRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Comment::class;
    }

    public function getConfirmedCommentsById($productId, $sortWith = 'created_at', $sortOrder = 'desc')
    {
        $model = $this->model->where('product_id', $productId)
            ->join('users', 'users.id', '=', 'comments.user_id')
            ->select([
                'name', 'comments.id as comment_id',
                'comments.body', 'comments.created_at', 'comments.like'
            ])
            ->where('status', Comment::CONFIRMED)
            ->orderBy($sortWith, $sortOrder)
            ->paginate(CommentController::PAGINATE_SIZE);

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
