<?php

declare(strict_types=1);

namespace App\Authentication\Application\UseCase\Login;

use App\Authentication\Application\DTO\AuthTokenDTO;
use App\Authentication\Application\Service\AuthTokenCreator;
use App\Authentication\Domain\Exception\InvalidCredentials;
use App\Authentication\Domain\Repository\ClientCredentialRepository;
use App\Authentication\Domain\Service\PasswordHasher;
use App\Authentication\Domain\ValueObject\Password;
use App\Authentication\Domain\ValueObject\Username;
use App\Common\Application\Command\CommandHandler;
use App\Common\Application\Query\QueryBus;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\UseCase\GetClientById\GetClientByIdQuery;

final class LoginCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly ClientCredentialRepository $clientCredentialRepository,
        private readonly PasswordHasher $passwordHasher,
        private readonly AuthTokenCreator $authTokenCreator,
    ) {
    }

    /**
     * @throws InvalidCredentials
     */
    public function __invoke(LoginCommand $command): AuthTokenDTO
    {
        try {
            $clientCredential = $this->clientCredentialRepository->getByUsername(Username::fromString($command->username));
        } catch (\Exception) {
            throw new InvalidCredentials();
        }

        if (!$this->passwordHasher->verify($clientCredential->hashedPassword(), Password::fromString($command->password))) {
            throw new InvalidCredentials();
        }

        /** @var ClientDTO $clientDTO */
        $clientDTO = $this->queryBus
            ->ask(
                new GetClientByIdQuery(
                    clientId: (string) $clientCredential->clientId(),
                )
            );

        return $this->authTokenCreator->createFromClientDTO($clientDTO);
    }
}
