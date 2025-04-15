<?php

declare(strict_types=1);

namespace App\ClientManagement\Infrastructure\Event;

use App\Authentication\Domain\Event\ClientHasBeenRegistered;
use App\ClientManagement\Domain\Service\EmailService;
use App\Common\Domain\Event\DomainEventSubscriber;
use App\Common\Application\Query\QueryBus;
use App\ClientManagement\Application\UseCase\GetClientById\GetClientByIdQuery;

final class SendWelcomeEmailWhenClientRegisteredSubscriber implements DomainEventSubscriber
{
    public function __construct(
        private readonly EmailService $emailService,
        private readonly QueryBus $queryBus,
    ) {
    }

    public static function subscribedTo(): array
    {
        return [ClientHasBeenRegistered::class];
    }

    public function __invoke(ClientHasBeenRegistered $event): void
    {
        $clientDTO = $this->queryBus->ask(
            new GetClientByIdQuery($event->clientId())
        );

        if ($clientDTO === null) {
            return;
        }

        $this->emailService->sendWelcomeEmail(
            to: $clientDTO->email,
            firstName: $clientDTO->firstName
        );
    }
}