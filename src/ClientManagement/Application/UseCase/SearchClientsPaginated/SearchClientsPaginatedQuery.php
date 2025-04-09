<?php

declare(strict_types=1);

namespace App\ClientManagement\Application\UseCase\SearchClientsPaginated;

use App\Common\Application\Query\Query;

final class SearchClientsPaginatedQuery implements Query
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $itemsPerPage = 20,
    ) {
    }
}
