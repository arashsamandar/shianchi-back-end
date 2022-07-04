<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface FavoriteRepository
 * @package namespace App\Repositories;
 */
interface FavoriteRepository extends RepositoryInterface
{
    //
    public function getStoreFavoriteByUser($userId);

    public function getProductFavoriteByUser($userId);

    public function userAddedThisStoreBefore($userId, $competitorId);

    public function userAddedThisProductBefore($userId, $competitorId);

    public function deleteFromFavorite($userId, $id);
}
