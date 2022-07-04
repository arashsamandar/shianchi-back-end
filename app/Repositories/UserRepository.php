<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface UserRepository
 * @package namespace App\Repositories;
 */
interface UserRepository extends RepositoryInterface
{
    public function saveAddresses($userId, $data);

    public function updateAddresses($userId, $addressId, $data);

    public function deleteAddresses($userId, $addressId);

    public function attachStoreToUser($userId, $storeId);

    public function attachProductToUser($userId, $productId);

    public function getAddresses($userId);
}
