<?php

declare(strict_types=1);

namespace App\ClientManagement\Domain\Event;

use App\Common\Domain\Event\DomainEventHandler;
use App\Common\Domain\Event\DomainEvent;

interface ClientHasBeenRegisteredEventHandler extends DomainEventHandler
{

}