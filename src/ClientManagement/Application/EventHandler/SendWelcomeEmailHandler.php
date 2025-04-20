<?php

declare(strict_types=1);

namespace App\ClientManagement\Application\EventHandler;

use App\ClientManagement\Application\UseCase\GetClientById\GetClientByIdQuery;
use App\ClientManagement\Domain\Event\ClientHasBeenRegisteredEvent;
use App\ClientManagement\Domain\Service\EmailService;
use App\Common\Application\Query\QueryBus;
use App\Common\Domain\Event\DomainEvent;
use App\Common\Domain\Event\DomainEventSubscriber;

final class SendWelcomeEmailHandler implements DomainEventSubscriber
{
    public function __construct(
        private readonly EmailService $emailService,
        private readonly QueryBus $queryBus,
    ) {
    }

    public function __invoke(DomainEvent $event): void
    {
        if (!$event instanceof ClientHasBeenRegisteredEvent) {
            return;
        }

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

    public static function subscribedTo(): array
    {
        return [
            ClientHasBeenRegisteredEvent::class
        ];
    }
}