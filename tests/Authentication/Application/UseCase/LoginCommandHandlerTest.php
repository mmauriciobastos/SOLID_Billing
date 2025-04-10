<?php

declare(strict_types=1);

namespace Tests\Authentication\Application\UseCase;

use App\Authentication\Application\DTO\AuthTokenDTO;
use App\Authentication\Application\Service\AuthTokenCreator;
use App\Authentication\Application\UseCase\Login\LoginCommand;
use App\Authentication\Application\UseCase\Login\LoginCommandHandler;
use App\Authentication\Domain\Entity\ClientCredential;
use App\Authentication\Domain\Exception\InvalidCredentials;
use App\Authentication\Domain\Repository\ClientCredentialRepository;
use App\Authentication\Domain\Service\PasswordHasher;
use App\Authentication\Domain\ValueObject\HashedPassword;
use App\Authentication\Domain\ValueObject\Password;
use App\Authentication\Domain\ValueObject\Username;
use App\Common\Application\Query\QueryBus;
use App\Common\Domain\ValueObject\Email;
use App\Common\Domain\ValueObject\FirstName;
use App\Common\Domain\ValueObject\LastName;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\UseCase\GetClientById\GetClientByIdQuery;
use App\ClientManagement\Domain\Entity\Client;
use PHPUnit\Framework\TestCase;

final class LoginCommandHandlerTest extends TestCase
{
    private const VALID_FIRST_NAME = 'John';
    private const VALID_LAST_NAME = 'Doe';
    private const VALID_EMAIL = 'john@example.com';
    private const VALID_USERNAME = 'john@example.com';
    private const VALID_PASSWORD = 'password123';
    private const VALID_TOKEN = 'token123';
    private const INVALID_USERNAME = 'invalid@example.com';
    private const INVALID_PASSWORD = 'wrongpassword';

    private $queryBus;
    private $clientCredentialRepository;
    private $passwordHasher;
    private $authTokenCreator;
    private LoginCommandHandler $handler;

    protected function setUp(): void
    {
        $this->queryBus = $this->createMock(QueryBus::class);
        $this->clientCredentialRepository = $this->createMock(ClientCredentialRepository::class);
        $this->passwordHasher = $this->createMock(PasswordHasher::class);
        $this->authTokenCreator = $this->createMock(AuthTokenCreator::class);

        $this->handler = new LoginCommandHandler(
            $this->queryBus,
            $this->clientCredentialRepository,
            $this->passwordHasher,
            $this->authTokenCreator
        );
    }

    private function createValidClient(): Client
    {
        return Client::create(
            firstName: FirstName::fromString(self::VALID_FIRST_NAME),
            lastName: LastName::fromString(self::VALID_LAST_NAME),
            email: Email::fromString(self::VALID_EMAIL)
        );
    }

    private function createValidClientDTO(): ClientDTO
    {
        return ClientDTO::fromEntity($this->createValidClient());
    }

    /**
     * @test
     * @group authentication
     * @group login
     */
    public function should_return_auth_token_when_credentials_are_valid(): void
    {
        // Arrange
        $authToken = AuthTokenDTO::fromString(self::VALID_TOKEN);
        $client = $this->createValidClient();
        $clientDTO = ClientDTO::fromEntity($client);

        $clientCredential = $this->createMock(ClientCredential::class);
        $clientCredential->method('clientId')->willReturn($client->id());

        $hashedPassword = $this->createMock(HashedPassword::class);
        $clientCredential->method('hashedPassword')->willReturn($hashedPassword);

        $this->clientCredentialRepository
            ->method('getByUsername')
            ->with(Username::fromString(self::VALID_USERNAME))
            ->willReturn($clientCredential);

        $this->passwordHasher
            ->method('verify')
            ->with($hashedPassword, Password::fromString(self::VALID_PASSWORD))
            ->willReturn(true);

        $this->queryBus
            ->method('ask')
            ->with(new GetClientByIdQuery((string) $clientCredential->clientId()))
            ->willReturn($clientDTO);

        $this->authTokenCreator
            ->method('createFromClientDTO')
            ->with($clientDTO)
            ->willReturn($authToken);

        // Act
        $command = new LoginCommand(self::VALID_USERNAME, self::VALID_PASSWORD);
        $result = $this->handler->__invoke($command);

        // Assert
        $this->assertSame(
            $authToken,
            $result,
            'Login with valid credentials should return the expected auth token'
        );
    }

    /**
     * @test
     * @group authentication
     * @group login
     * @group validation
     */
    public function should_throw_invalid_credentials_when_username_not_found(): void
    {
        // Arrange
        $this->clientCredentialRepository
            ->method('getByUsername')
            ->willThrowException(new \Exception());

        $this->expectException(InvalidCredentials::class);
        $this->expectExceptionMessage('Invalid credentials provided');

        // Act & Assert
        $command = new LoginCommand(self::INVALID_USERNAME, self::VALID_PASSWORD);
        $this->handler->__invoke($command);
    }

    /**
     * @test
     * @group authentication
     * @group login
     * @group validation
     */
    public function should_throw_invalid_credentials_when_password_is_wrong(): void
    {
        // Arrange
        $clientCredential = $this->createMock(ClientCredential::class);
        $hashedPassword = $this->createMock(HashedPassword::class);
        $clientCredential->method('hashedPassword')->willReturn($hashedPassword);

        $this->clientCredentialRepository
            ->method('getByUsername')
            ->with(Username::fromString(self::VALID_USERNAME))
            ->willReturn($clientCredential);

        $this->passwordHasher
            ->method('verify')
            ->with($hashedPassword, Password::fromString(self::INVALID_PASSWORD))
            ->willReturn(false);

        $this->expectException(InvalidCredentials::class);
        $this->expectExceptionMessage('Invalid credentials provided');

        // Act & Assert
        $command = new LoginCommand(self::VALID_USERNAME, self::INVALID_PASSWORD);
        $this->handler->__invoke($command);
    }
}
