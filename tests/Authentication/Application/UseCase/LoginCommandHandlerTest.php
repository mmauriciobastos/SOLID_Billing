<?php

declare(strict_types=1);

namespace Tests\Authentication\Application\UseCase;

use App\Authentication\Application\DTO\AuthTokenDTO;
use App\Authentication\Application\Service\AuthTokenCreator;
use App\Authentication\Application\UseCase\Login\LoginCommand;
use App\Authentication\Application\UseCase\Login\LoginCommandHandler;
use App\Authentication\Domain\Entity\UserCredential;
use App\Authentication\Domain\Exception\InvalidCredentials;
use App\Authentication\Domain\Repository\UserCredentialRepository;
use App\Authentication\Domain\Service\PasswordHasher;
use App\Authentication\Domain\ValueObject\HashedPassword;
use App\Authentication\Domain\ValueObject\Password;
use App\Authentication\Domain\ValueObject\Username;
use App\Common\Application\Query\QueryBus;
use App\User\Application\DTO\UserDTO;
use App\User\Application\UseCase\GetUserById\GetUserByIdQuery;
use App\User\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class LoginCommandHandlerTest extends TestCase
{
    private $queryBus;
    private $userCredentialRepository;
    private $passwordHasher;
    private $authTokenCreator;
    private LoginCommandHandler $handler;

    protected function setUp(): void
    {
        \DG\BypassFinals::debugInfo();

        $this->queryBus = $this->createMock(QueryBus::class);
        $this->userCredentialRepository = $this->createMock(UserCredentialRepository::class);
        $this->passwordHasher = $this->createMock(PasswordHasher::class);
        $this->authTokenCreator = $this->createMock(AuthTokenCreator::class);

        $this->handler = new LoginCommandHandler(
            $this->queryBus,
            $this->userCredentialRepository,
            $this->passwordHasher,
            $this->authTokenCreator
        );
    }

    public function testValidLogin(): void
    {
        $userId = UserId::generate();
        $firstName = 'John';
        $lastName = 'Doe';
        $email = 'john@example.com';
        $username = 'john@example.com';
        $password = 'password123';
        $authToken = AuthTokenDTO::fromString('token123');

        $userCredential = $this->createMock(UserCredential::class);
        $hashedPassword = $this->createMock(HashedPassword::class);
        $userCredential->method('hashedPassword')->willReturn($hashedPassword);
        $userCredential->method('userId')->willReturn($userId);

        $userDTO = $this->createMock(UserDTO::class);

        $this->userCredentialRepository
            ->method('getByUsername')
            ->with(Username::fromString($username))
            ->willReturn($userCredential);

        $this->passwordHasher
            ->method('verify')
            ->with('hashed_password', Password::fromString($password))
            ->willReturn(true);

        $this->queryBus
            ->method('ask')
            ->with(new GetUserByIdQuery($userId->value()))
            ->willReturn($userDTO);

        $this->authTokenCreator
            ->method('createFromUserDTO')
            ->with($userDTO)
            ->willReturn($authToken);

        $command = new LoginCommand($username, $password);
        $result = $this->handler->__invoke($command);

        $this->assertSame($authToken, $result);
    }

    public function testInvalidUsername(): void
    {
        $this->userCredentialRepository
            ->method('getByUsername')
            ->willThrowException(new \Exception());

        $this->expectException(InvalidCredentials::class);

        $command = new LoginCommand('invaliduser', 'password123');
        $this->handler->__invoke($command);
    }

    public function testInvalidPassword(): void
    {
        $username = 'john@example.com';
        $password = 'wrongpassword';

        $userCredential = $this->createMock(UserCredential::class);
        $hashedPassword = $this->createMock(HashedPassword::class);

        $userCredential->method('hashedPassword')->willReturn($hashedPassword);

        $this->userCredentialRepository
            ->method('getByUsername')
            ->with(Username::fromString($username))
            ->willReturn($userCredential);

        $this->passwordHasher
            ->method('verify')
            ->with($hashedPassword, Password::fromString($password))
            ->willReturn(false);

        $this->expectException(InvalidCredentials::class);

        $command = new LoginCommand($username, $password);
        $this->handler->__invoke($command);
    }
}
