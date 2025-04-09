<?php

declare(strict_types=1);

namespace App\Authentication\Application\Service;

use App\Authentication\Application\DTO\AuthTokenDTO;
use App\ClientManagement\Application\DTO\ClientDTO;

final class AuthTokenCreator
{
    public function __construct(
        private readonly TokenEncoder $tokenEncoder,
    ) {
    }

    public function createFromClientDTO(ClientDTO $clientDTO): AuthTokenDTO
    {
        $tokenPayload = [
            'clientId' => $clientDTO->id,
            'email' => $clientDTO->email,
            'firstName' => $clientDTO->firstName,
            'lastName' => $clientDTO->lastName,
        ];

        return AuthTokenDTO::fromString($this->tokenEncoder->encode($tokenPayload));
    }
}
