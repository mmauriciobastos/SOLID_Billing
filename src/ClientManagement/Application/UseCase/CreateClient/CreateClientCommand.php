<?php

declare(strict_types=1);

namespace App\ClientManagement\Application\UseCase\CreateClient;

use App\Common\Application\Command\Command;

final class CreateClientCommand implements Command
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
    ) {
    }
}
