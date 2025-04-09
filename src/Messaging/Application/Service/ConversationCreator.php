<?php

declare(strict_types=1);

namespace App\Messaging\Application\Service;

use App\Common\Application\Query\QueryBus;
use App\Common\Domain\ValueObject\DateTime;
use App\Messaging\Domain\Entity\Conversation;
use App\Messaging\Domain\Repository\ConversationRepository;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\UseCase\GetClientById\GetClientByIdQuery;

final class ConversationCreator
{
    public function __construct(
        private readonly ConversationRepository $conversationRepository,
        private readonly QueryBus $queryBus,
    ) {
    }

    public function createConversation(array $clientsIds): Conversation
    {
        $clientsDTOs = $this->getClientsDTOsFromClientsIds($clientsIds);

        $conversation = Conversation::create(DateTime::now(), $clientsDTOs);

        $this->conversationRepository->add($conversation);

        return $conversation;
    }

    /**
     * @param int[] $clientsIds
     * @return ClientDTO[]
     */
    private function getClientsDTOsFromClientsIds(array $clientsIds): array
    {
        /** @var ClientDTO[] $clients */
        return array_map(function (string $clientId) {
            return $this->queryBus->ask(new GetClientByIdQuery(clientId: $clientId));
        }, $clientsIds);
    }
}
