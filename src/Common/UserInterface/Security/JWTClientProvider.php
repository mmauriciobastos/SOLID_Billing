<?php

declare(strict_types=1);

namespace App\Common\UserInterface\Security;

use App\Authentication\Application\UseCase\GetAuthClientFromToken\GetAuthClientFromTokenQuery;
use App\Common\Application\Query\QueryBus;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class JWTClientProvider implements UserProviderInterface
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return AuthClient::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): AuthClient
    {
        try {
            $authClientDTO = $this->queryBus
                ->ask(
                    new GetAuthClientFromTokenQuery(
                        token: self::extractToken($identifier),
                    )
                );
        } catch (\Exception $exception) {
            throw new CustomUserMessageAuthenticationException($exception->getMessage());
        }

        return AuthClient::fromAuthClientDTO($authClientDTO);
    }

    /**
     * @throws CustomUserMessageAuthenticationException
     */
    private static function extractToken(string $identifier): string
    {
        $explodeAuthHeader = explode(' ', $identifier);

        if (2 !== count($explodeAuthHeader) || $explodeAuthHeader[0] !== 'Bearer') {
            throw new CustomUserMessageAuthenticationException();
        }

        $bearerToken = $explodeAuthHeader[1];
        $explodeJwtParts = explode('.', $bearerToken);

        if (3 !== count($explodeJwtParts)) {
            throw new CustomUserMessageAuthenticationException();
        }

        return $bearerToken;
    }
}
