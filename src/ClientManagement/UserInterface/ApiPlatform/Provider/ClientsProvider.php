<?php

declare(strict_types=1);

namespace App\ClientManagement\UserInterface\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\Common\Application\Query\QueryBus;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\UseCase\SearchClientsPaginated\SearchClientsPaginatedQuery;
use App\ClientManagement\UserInterface\ApiPlatform\Resource\ClientResource;

/**
 * @template-implements ProviderInterface<ClientResource>
 */
final class ClientsProvider implements ProviderInterface
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly Pagination $pagination,
    ) {
    }

    /**
     * @return ClientResource[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $page = $this->pagination->getPage($context);
        $itemsPerPage = $this->pagination->getLimit($operation, $context);

        $clients = $this->getClientsDTOs($page, $itemsPerPage);

        return $this->mapClientDTOsToClientsResources($clients);
    }

    /**
     * @return ClientDTO[]
     */
    private function getClientsDTOs(int $page, int $itemsPerPage): array
    {
        return $this->queryBus->ask(new SearchClientsPaginatedQuery($page, $itemsPerPage));
    }

    /**
     * @return ClientResource[]
     */
    private function mapClientDTOsToClientsResources(array $clientsDTOs): array
    {
        $resources = [];
        foreach ($clientsDTOs as $clientDTO) {
            $resources[] = ClientResource::fromClientDTO($clientDTO);
        }

        return $resources;
    }
}
