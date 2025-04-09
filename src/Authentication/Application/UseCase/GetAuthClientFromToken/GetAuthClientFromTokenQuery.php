<?php

declare(strict_types=1);

namespace App\Authentication\Application\UseCase\GetAuthClientFromToken;

use App\Common\Application\Query\Query;

final class GetAuthClientFromTokenQuery implements Query
{
    public function __construct(
        public readonly string $token,
    ) {
    }
}
