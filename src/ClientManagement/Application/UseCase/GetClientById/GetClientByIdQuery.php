<?php

declare(strict_types=1);

namespace App\ClientManagement\Application\UseCase\GetClientById;

use App\Common\Application\Query\Query;

final class GetClientByIdQuery implements Query
{
    public function __construct(
        public readonly string $clientId,
    ) {
    }
}
