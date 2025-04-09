<?php

declare(strict_types=1);

namespace App\ClientManagement\Application\UseCase\GetClientById;

use App\Common\Application\Query\QueryHandler;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Domain\Exception\ClientNotFound;
use App\ClientManagement\Domain\Repository\ClientRepository;
use App\ClientManagement\Domain\ValueObject\ClientId;

final class GetClientByIdQueryHandler implements QueryHandler
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
    ) {
    }

    /**
     * @throws ClientNotFound
     */
    public function __invoke(GetClientByIdQuery $query): ?ClientDTO
    {
        $client = $this->clientRepository->get(ClientId::fromString($query->clientId));

        return ClientDTO::fromEntity($client);
    }
}
