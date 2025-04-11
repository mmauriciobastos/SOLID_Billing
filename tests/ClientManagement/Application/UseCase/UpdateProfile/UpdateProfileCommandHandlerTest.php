<?php

declare(strict_types=1);

namespace Tests\ClientManagement\Application\UseCase\UpdateProfile;

use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\UseCase\UpdateProfile\UpdateProfileCommand;
use App\ClientManagement\Application\UseCase\UpdateProfile\UpdateProfileCommandHandler;
use App\ClientManagement\Domain\Entity\Client;
use App\ClientManagement\Domain\Exception\ClientNotFound;
use App\ClientManagement\Domain\Exception\ClientNotFoundWithId;
use App\ClientManagement\Domain\Exception\EmailAlreadyUsed;
use App\ClientManagement\Domain\Repository\ClientRepository;
use App\ClientManagement\Domain\ValueObject\ClientId;
use App\Common\Domain\Exception\InvalidFormat;
use App\Common\Domain\ValueObject\Email;
use App\Common\Domain\ValueObject\FirstName;
use App\Common\Domain\ValueObject\LastName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateProfileCommandHandlerTest extends TestCase
{
    private const VALID_CLIENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const VALID_EMAIL = 'john.doe@example.com';
    private const NEW_VALID_EMAIL = 'john.new@example.com';
    private const VALID_FIRST_NAME = 'John';
    private const NEW_VALID_FIRST_NAME = 'Johnny';
    private const VALID_LAST_NAME = 'Doe';
    private const NEW_VALID_LAST_NAME = 'Smith';
    private const INVALID_EMAIL = 'invalid-email';
    private const EXISTING_EMAIL = 'existing@example.com';

    private ClientRepository|MockObject $clientRepository;
    private UpdateProfileCommandHandler $handler;
    private Client $existingClient;

    protected function setUp(): void
    {
        $this->clientRepository = $this->createMock(ClientRepository::class);
        $this->handler = new UpdateProfileCommandHandler($this->clientRepository);
        
        $this->existingClient = Client::create(
            FirstName::fromString(self::VALID_FIRST_NAME),
            LastName::fromString(self::VALID_LAST_NAME),
            Email::fromString(self::VALID_EMAIL)
        );
    }

    /**
     * @test
     * @group client-management
     * @group profile
     */
    public function should_update_client_when_data_is_valid(): void
    {
        // Arrange
        $command = new UpdateProfileCommand(
            clientId: self::VALID_CLIENT_ID,
            email: self::NEW_VALID_EMAIL,
            firstName: self::NEW_VALID_FIRST_NAME,
            lastName: self::NEW_VALID_LAST_NAME
        );

        $this->clientRepository
            ->method('get')
            ->with($this->isInstanceOf(ClientId::class))
            ->willReturn($this->existingClient);

        $this->clientRepository
            ->method('emailExist')
            ->with($this->isInstanceOf(Email::class))
            ->willReturn(false);

        // Act
        $result = $this->handler->__invoke($command);

        // Assert
        $this->assertInstanceOf(
            ClientDTO::class, 
            $result,
            'Handler should return a ClientDTO instance'
        );
        $this->assertSame(
            self::NEW_VALID_EMAIL,
            $result->email,
            'Client email should be updated to the new email'
        );
        $this->assertSame(
            self::NEW_VALID_FIRST_NAME,
            $result->firstName,
            'Client first name should be updated to the new first name'
        );
        $this->assertSame(
            self::NEW_VALID_LAST_NAME,
            $result->lastName,
            'Client last name should be updated to the new last name'
        );
    }

    /**
     * @test
     * @group client-management
     * @group profile
     */
    public function should_throw_exception_when_client_not_found(): void
    {
        // Arrange
        $command = new UpdateProfileCommand(
            clientId: self::VALID_CLIENT_ID,
            email: self::NEW_VALID_EMAIL,
            firstName: self::NEW_VALID_FIRST_NAME,
            lastName: self::NEW_VALID_LAST_NAME
        );

        $this->clientRepository
            ->method('get')
            ->willThrowException(new ClientNotFoundWithId(ClientId::fromString(self::VALID_CLIENT_ID)));

        $this->expectException(ClientNotFound::class);

        // Act & Assert
        $this->handler->__invoke($command);
    }

    /**
     * @test
     * @group client-management
     * @group profile
     * @group validation
     */
    public function should_throw_exception_when_new_email_already_exists(): void
    {
        // Arrange
        $command = new UpdateProfileCommand(
            clientId: self::VALID_CLIENT_ID,
            email: self::EXISTING_EMAIL,
            firstName: self::NEW_VALID_FIRST_NAME,
            lastName: self::NEW_VALID_LAST_NAME
        );

        $this->clientRepository
            ->method('get')
            ->willReturn($this->existingClient);

        $this->clientRepository
            ->method('emailExist')
            ->willReturn(true);

        $this->expectException(EmailAlreadyUsed::class);

        // Act & Assert
        $this->handler->__invoke($command);
    }

    /**
     * @test
     * @group client-management
     * @group profile
     * @group validation
     */
    public function should_throw_exception_when_email_format_is_invalid(): void
    {
        // Arrange
        $command = new UpdateProfileCommand(
            clientId: self::VALID_CLIENT_ID,
            email: self::INVALID_EMAIL,
            firstName: self::NEW_VALID_FIRST_NAME,
            lastName: self::NEW_VALID_LAST_NAME
        );

        $this->expectException(InvalidFormat::class);

        // Act & Assert
        $this->handler->__invoke($command);
    }

    /**
     * @test
     * @group client-management
     * @group profile
     * @group validation
     */
    public function should_throw_exception_when_first_name_is_empty(): void
    {
        // Arrange
        $command = new UpdateProfileCommand(
            clientId: self::VALID_CLIENT_ID,
            email: self::NEW_VALID_EMAIL,
            firstName: '',
            lastName: self::NEW_VALID_LAST_NAME
        );

        $this->expectException(InvalidFormat::class);

        // Act & Assert
        $this->handler->__invoke($command);
    }

    /**
     * @test
     * @group client-management
     * @group profile
     * @group validation
     */
    public function should_throw_exception_when_last_name_is_empty(): void
    {
        // Arrange
        $command = new UpdateProfileCommand(
            clientId: self::VALID_CLIENT_ID,
            email: self::NEW_VALID_EMAIL,
            firstName: self::NEW_VALID_FIRST_NAME,
            lastName: ''
        );

        $this->expectException(InvalidFormat::class);

        // Act & Assert
        $this->handler->__invoke($command);
    }
}