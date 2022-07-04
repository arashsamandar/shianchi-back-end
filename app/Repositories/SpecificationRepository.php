<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface SpecificationRepository
 * @package namespace App\Repositories;
 */
interface SpecificationRepository extends RepositoryInterface
{
    //
    public function getValues($id);
}
