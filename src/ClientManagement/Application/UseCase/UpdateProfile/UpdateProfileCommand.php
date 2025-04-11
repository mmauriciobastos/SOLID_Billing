<?php

declare(strict_types=1);

namespace App\ClientManagement\Application\UseCase\UpdateProfile;

use App\Common\Application\Command\Command;

final class UpdateProfileCommand implements Command
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName
    ) {
    }
}