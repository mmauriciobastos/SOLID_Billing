<?php

declare(strict_types=1);

namespace App\ClientManagement\Application\EventHandler;

use App\ClientManagement\Application\UseCase\GetClientById\GetClientByIdQuery;
use App\ClientManagement\Domain\Event\ClientHasBeenRegisteredEvent;
use App\ClientManagement\Domain\Event\ClientHasBeenRegisteredEventHandler;
use App\ClientManagement\Domain\Service\EmailService;
use App\Common\Application\Query\QueryBus;
use App\Common\Domain\Event\DomainEvent;

final class WelcomeEmailSender implements ClientHasBeenRegisteredEventHandler
{
    public function __construct(
        private readonly EmailService $emailService,
        private readonly QueryBus $queryBus,
    ) {
    }

    public function handle(DomainEvent $event): void
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
}