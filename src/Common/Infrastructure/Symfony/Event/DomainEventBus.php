<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Symfony\Event;

use App\Common\Domain\Event\EventBus;
use App\Common\Domain\Event\DomainEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DomainEventBus implements EventBus
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @inheritDoc
     */
    public function publish(DomainEvent ...$events): void {
        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event, $event::eventName());
        }
    }
}