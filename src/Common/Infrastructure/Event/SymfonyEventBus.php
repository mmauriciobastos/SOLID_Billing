<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Event;

use App\Common\Application\Event\EventBus;
use App\Common\Domain\Event\DomainEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class SymfonyEventBus implements EventBus
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function dispatch(DomainEvent $event): void
    {
        $this->eventDispatcher->dispatch($event, $event::eventName());
    }
}