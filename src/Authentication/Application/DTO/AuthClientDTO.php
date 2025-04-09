<?php

declare(strict_types=1);

namespace App\Authentication\Application\DTO;

use App\ClientManagement\Application\DTO\ClientDTO;

final class AuthClientDTO
{
    private function __construct(
        public readonly string $clientId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
    ) {
    }

    public static function createFromClientDTO(ClientDTO $clientDTO): AuthClientDTO
    {
        return new self(
            clientId: $clientDTO->id,
            firstName: $clientDTO->firstName,
            lastName: $clientDTO->lastName,
            email: $clientDTO->email,
        );
    }

    public static function createFromJWTPayload(array $jwtPayload): AuthClientDTO
    {
        return new self(
            clientId: $jwtPayload['clientId'],
            firstName: $jwtPayload['firstName'],
            lastName: $jwtPayload['lastName'],
            email: $jwtPayload['email'],
        );
    }
}
