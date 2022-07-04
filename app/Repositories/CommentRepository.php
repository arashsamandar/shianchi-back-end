<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface CommentRepository
 * @package namespace App\Repositories;
 */
interface CommentRepository extends RepositoryInterface
{
    public function getConfirmedCommentsById($productId, $sortWith = 'created_at', $sortOrder = 'desc');
}
