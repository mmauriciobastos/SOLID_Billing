<?php

declare(strict_types=1);

namespace App\ClientManagement\Application\UseCase\RegisterClient;

use App\Common\Application\Command\Command;

final class RegisterClientCommand implements Command
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
    ) {
    }
}
