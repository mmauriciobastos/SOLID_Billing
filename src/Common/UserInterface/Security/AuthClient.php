<?php

declare(strict_types=1);

namespace App\Common\UserInterface\Security;

use App\Authentication\Application\DTO\AuthClientDTO;
use Symfony\Component\Security\Core\User\UserInterface;

final class AuthClient implements UserInterface
{
    private function __construct(
        private readonly AuthClientDTO $authClientDTO,
    ) {
    }

    public static function fromAuthClientDTO(AuthClientDTO $authClientDTO): self
    {
        return new self($authClientDTO);
    }

    public function authClientDTO(): AuthClientDTO
    {
        return $this->authClientDTO;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->authClientDTO->clientId;
    }
}
