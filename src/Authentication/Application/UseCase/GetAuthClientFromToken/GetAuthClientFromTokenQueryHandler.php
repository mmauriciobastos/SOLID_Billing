<?php

declare(strict_types=1);

namespace App\Authentication\Application\UseCase\GetAuthClientFromToken;

use App\Authentication\Application\DTO\AuthClientDTO;
use App\Authentication\Application\Service\TokenDecoder;
use App\Common\Application\Query\QueryHandler;

final class GetAuthClientFromTokenQueryHandler implements QueryHandler
{
    public function __construct(
        private readonly TokenDecoder $tokenDecoder,
    ) {
    }

    public function __invoke(GetAuthClientFromTokenQuery $query): AuthClientDTO
    {
        return AuthClientDTO::createFromJWTPayload($this->tokenDecoder->decode($query->token));
    }
}
