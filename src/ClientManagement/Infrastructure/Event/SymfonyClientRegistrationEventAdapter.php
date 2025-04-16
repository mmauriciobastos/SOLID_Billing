<?php

declare(strict_types=1);

namespace App\ClientManagement\Infrastructure\Event;

use App\ClientManagement\Domain\Event\ClientHasBeenRegisteredEvent;
use App\ClientManagement\Domain\Event\ClientHasBeenRegisteredEventHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SymfonyClientRegistrationEventAdapter implements EventSubscriberInterface
{
    public function __construct(
        private readonly ClientHasBeenRegisteredEventHandler $eventHandler
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ClientHasBeenRegisteredEvent::EVENT_NAME => ['onClientHasBeenRegisteredEvent'],
        ];
    }

    public function onClientHasBeenRegisteredEvent(ClientHasBeenRegisteredEvent $event): void
    {
        $this->eventHandler->handle($event);
    }
}