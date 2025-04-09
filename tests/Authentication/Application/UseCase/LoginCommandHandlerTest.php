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
use PHPUnit\Framework\TestCase;

final class LoginCommandHandlerTest extends TestCase
{
    private $queryBus;
    private $clientCredentialRepository;
    private $passwordHasher;
    private $authTokenCreator;
    private LoginCommandHandler $handler;

    protected function setUp(): void
    {
        \DG\BypassFinals::debugInfo();

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

    public function testValidLogin(): void
    {
        $firstName = 'John';
        $lastName = 'Doe';
        $email = 'john@example.com';
        $username = 'john@example.com';
        $password = 'password123';
        $authToken = AuthTokenDTO::fromString('token123');

        $clientCredential = $this->createMock(ClientCredential::class);

        $client = \App\ClientManagement\Domain\Entity\Client::create(
            firstName: FirstName::fromString($firstName),
            lastName: LastName::fromString($lastName),
            email: Email::fromString($email)
        );
        $clientDTO = ClientDTO::fromEntity($client);
        $clientCredential->method('clientId')->willReturn($client->id());

        $hashedPassword = $this->createMock(HashedPassword::class);
        $clientCredential->method('hashedPassword')->willReturn($hashedPassword);

        $this->clientCredentialRepository
            ->method('getByUsername')
            ->with(Username::fromString($username))
            ->willReturn($clientCredential);

        $this->passwordHasher
            ->method('verify')
            ->with($hashedPassword, Password::fromString($password))
            ->willReturn(true);

        $this->queryBus
            ->method('ask')
            ->with(new GetClientByIdQuery((string) $clientCredential->clientId()))
            ->willReturn($clientDTO);

        $this->authTokenCreator
            ->method('createFromClientDTO')
            ->with($clientDTO)
            ->willReturn($authToken);

        $command = new LoginCommand($username, $password);
        $result = $this->handler->__invoke($command);

        $this->assertEquals($authToken, $result);
    }

    public function testInvalidClientname(): void
    {
        $this->clientCredentialRepository
            ->method('getByClientname')
            ->willThrowException(new \Exception());

        $this->expectException(InvalidCredentials::class);

        $command = new LoginCommand('invalidclient', 'password123');
        $this->handler->__invoke($command);
    }

    public function testInvalidPassword(): void
    {
        $username = 'john@example.com';
        $password = 'wrongpassword';

        $clientCredential = $this->createMock(ClientCredential::class);
        $hashedPassword = $this->createMock(HashedPassword::class);

        $clientCredential->method('hashedPassword')->willReturn($hashedPassword);

        $this->clientCredentialRepository
            ->method('getByUsername')
            ->with(UserName::fromString($username))
            ->willReturn($clientCredential);

        $this->passwordHasher
            ->method('verify')
            ->with($hashedPassword, Password::fromString($password))
            ->willReturn(false);

        $this->expectException(InvalidCredentials::class);

        $command = new LoginCommand($username, $password);
        $this->handler->__invoke($command);
    }
}
