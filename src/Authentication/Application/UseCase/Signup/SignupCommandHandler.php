<?php

declare(strict_types=1);

namespace App\Authentication\Application\UseCase\Signup;

use App\Authentication\Application\DTO\AuthTokenDTO;
use App\Authentication\Application\Service\AuthTokenCreator;
use App\Authentication\Domain\Entity\ClientCredential;
use App\Authentication\Domain\Exception\CredentialNotFoundForUsername;
use App\Authentication\Domain\Exception\UsernameAlreadyUsed;
use App\Authentication\Domain\Repository\ClientCredentialRepository;
use App\Authentication\Domain\Service\PasswordHasher;
use App\Authentication\Domain\ValueObject\Password;
use App\Authentication\Domain\ValueObject\Username;
use App\Common\Application\Command\CommandBus;
use App\Common\Application\Command\CommandHandler;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\UseCase\CreateClient\CreateClientCommand;
use App\ClientManagement\Domain\ValueObject\ClientId;

final class SignupCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly PasswordHasher $passwordHasher,
        private readonly ClientCredentialRepository $clientCredentialRepository,
        private readonly AuthTokenCreator $authTokenCreator,
    ) {
    }

    /**
     * @throws UsernameAlreadyUsed
     */
    public function __invoke(SignupCommand $command): AuthTokenDTO
    {
        $this->ensurePasswordConfirmIsValid(Password::fromString($command->password), Password::fromString($command->passwordConfirm));
        $this->ensureUsernameIsAvailable(Username::fromString($command->email));

        // Client is created first for generate ClientId to use in creating of credentials
        $clientDTO = $this->createClient(
            firstName: $command->firstName,
            lastName: $command->lastName,
            email: $command->email,
        );

        $clientCredential = ClientCredential::create(
            clientId: ClientId::fromString($clientDTO->id),
            username: Username::fromString($command->email),
            hashedPassword: $this->passwordHasher->hash(Password::fromString($command->password)),
        );

        $this->clientCredentialRepository->add($clientCredential);

        return $this->authTokenCreator->createFromClientDTO($clientDTO);
    }

    private function ensurePasswordConfirmIsValid(Password $password, Password $passwordConfirm): void
    {
        if (!$password->isEqual($passwordConfirm)) {
            throw new \RuntimeException('Invalid password confirm');
        }
    }

    /**
     * @throws UsernameAlreadyUsed
     */
    private function ensureUsernameIsAvailable(Username $username): void
    {
        try {
            $this->clientCredentialRepository->getByUsername($username);
        } catch (CredentialNotFoundForUsername) {
            return;
        }

        throw new UsernameAlreadyUsed($username);
    }

    private function createClient(string $firstName, string $lastName, string $email): ClientDTO
    {
        return $this->commandBus
            ->dispatch(
                new CreateClientCommand(
                    firstName: $firstName,
                    lastName: $lastName,
                    email: $email,
                )
            );
    }
}
