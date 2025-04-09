<?php

declare(strict_types=1);

namespace App\ClientManagement\Application\UseCase\SearchClientsPaginated;

use App\Common\Application\Query\QueryHandler;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Domain\Entity\Client;
use App\ClientManagement\Domain\Repository\ClientRepository;

final class SearchClientsPaginatedQueryHandler implements QueryHandler
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
    ) {
    }

    public function __invoke(SearchClientsPaginatedQuery $query): array
    {
        $clients = $this->clientRepository->search($query->page, $query->itemsPerPage);

        return $this->mapClientsToClientsDTOs($clients);
    }

    /**
     * @param Client[] $clients
     * @return ClientDTO[]
     */
    private function mapClientsToClientsDTOs(array $clients): array
    {
        return array_map(static function (Client $client) {
            return ClientDTO::fromEntity($client);
        }, $clients);
    }
}
