<?php

declare(strict_types=1);

namespace App\Common\Application\Event;

use App\Common\Domain\Event\DomainEvent;

interface EventBus
{
    public function dispatch(DomainEvent $event): void;
}