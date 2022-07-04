<?php

namespace App\Repositories\Category;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface CategoryRepository
 * @package namespace App\Repositories;
 */
interface CategoryRepository extends RepositoryInterface
{
    public function getTitles($id);

    public function detachNotMentionedTitles($id, $titleIds);

    public function getAllCategoriesTitles($categoryIds, $columns = ['*']);

    public function getBrands($categoryId);

}
