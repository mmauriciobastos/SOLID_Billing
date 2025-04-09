<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Symfony\Security;

use App\Authentication\Application\DTO\AuthClientDTO;
use App\Common\Application\Session\Security;
use Symfony\Bundle\SecurityBundle\Security as SecurityComponent;
use App\Common\UserInterface\Security\AuthClient;

final class SecurityService implements Security
{
    public function __construct(
        private readonly SecurityComponent $security,
    ) {
    }

    public function isAuthenticated(): bool
    {
        return null !== $this->connectedClient();
    }

    public function connectedClient(): ?AuthClientDTO
    {
        $authenticatedClient = $this->security->getUser();

        if (!$authenticatedClient instanceof AuthClient) {
            return null;
        }

        return $authenticatedClient->authClientDTO();
    }
}
