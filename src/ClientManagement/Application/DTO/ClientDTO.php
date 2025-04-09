<?php

declare(strict_types=1);

namespace App\ClientManagement\Application\DTO;

use App\ClientManagement\Domain\Entity\Client;

final class ClientDTO
{
    private function __construct(
        public readonly string $id,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
    ) {
    }

    public static function fromEntity(Client $client): self
    {
        return new self(
            id: (string) $client->id(),
            firstName: (string) $client->firstName(),
            lastName: (string) $client->lastName(),
            email: (string) $client->email(),
        );
    }
}
