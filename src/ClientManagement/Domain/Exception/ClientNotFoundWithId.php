<?php

declare(strict_types=1);

namespace App\ClientManagement\Domain\Exception;

use App\ClientManagement\Domain\ValueObject\ClientId;

final class ClientNotFoundWithId extends ClientNotFound
{
    public function __construct(ClientId $clientId)
    {
        parent::__construct(sprintf('Client not found with id "%s".', (string) $clientId));
    }
}
