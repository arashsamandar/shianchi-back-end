<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface ValueRepository
 * @package namespace App\Repositories;
 */
interface ValueRepository extends RepositoryInterface
{
    public function deleteNotMentionedSpecificationValues($specificationId, $valueIds);
}
