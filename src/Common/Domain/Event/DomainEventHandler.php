<?php

declare(strict_types=1);

namespace App\Common\Domain\Event;

interface DomainEventHandler
{
    public function handle(DomainEvent $event): void;
}
