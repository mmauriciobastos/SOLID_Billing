<?php

declare(strict_types=1);

namespace App\ClientManagement\Domain\Event;

use App\Common\Domain\Event\DomainEvent;
use App\ClientManagement\Domain\ValueObject\ClientId;

final class ClientHasBeenRegisteredEvent extends DomainEvent
{
    public const EVENT_NAME = 'authentication.client_has_been_registered';

    public static function eventName(): string
    {
        return self::EVENT_NAME;
    }

    public static function withClientId(string $clientId): self
    {
        return self::create(
            ClientId::fromString($clientId)
        );
    }

    public function clientId(): string
    {
        return (string) $this->aggregateRootId();
    }
}
