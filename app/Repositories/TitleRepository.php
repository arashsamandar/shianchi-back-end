<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface TitleRepository
 * @package namespace App\Repositories;
 */
interface TitleRepository extends RepositoryInterface
{
    //
    public function attachCategories($id, $categoryIds);
}
