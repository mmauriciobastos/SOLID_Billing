<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Event;

use App\Common\Domain\Event\DomainEvent;

final class ClientPasswordHasBeenChanged extends DomainEvent
{
    public const EVENT_NAME = 'authentication.client_password_has_been_changed';

    public static function eventName(): string
    {
        return self::EVENT_NAME;
    }
}
