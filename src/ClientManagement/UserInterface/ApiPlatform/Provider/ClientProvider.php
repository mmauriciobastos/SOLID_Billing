<?php

declare(strict_types=1);

namespace App\ClientManagement\UserInterface\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Common\Application\Query\QueryBus;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\UseCase\GetClientById\GetClientByIdQuery;
use App\ClientManagement\Domain\Exception\ClientNotFound;
use App\ClientManagement\UserInterface\ApiPlatform\Resource\ClientResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template-implements ProviderInterface<ClientResource>
 */
final class ClientProvider implements ProviderInterface
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        try {
            $clientId = (string) $uriVariables['id'];
            $clientDTO = $this->getClientById($clientId);
        } catch (ClientNotFound $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }

        return ClientResource::fromClientDTO($clientDTO);
    }

    /**
     * @throws ClientNotFound
     */
    private function getClientById(string $clientId): ClientDTO
    {
        return $this->queryBus->ask(new GetClientByIdQuery($clientId));
    }
}
