<?php

declare(strict_types=1);

namespace Tests\ClientManagement\Application\UseCase\RegisterClient;

use App\Authentication\Domain\Event\ClientHasBeenRegistered;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\UseCase\RegisterClient\RegisterClientCommand;
use App\ClientManagement\Application\UseCase\RegisterClient\RegisterClientCommandHandler;
use App\ClientManagement\Domain\Entity\Client;
use App\ClientManagement\Domain\Exception\EmailAlreadyUsed;
use App\ClientManagement\Domain\Repository\ClientRepository;
use App\Common\Application\Event\EventBus;
use App\Common\Domain\Exception\InvalidFormat;
use App\Common\Domain\ValueObject\Email;
use App\Common\Domain\ValueObject\FirstName;
use App\Common\Domain\ValueObject\LastName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RegisterClientCommandHandlerTest extends TestCase
{
    private const VALID_EMAIL = 'john.doe@example.com';
    private const VALID_FIRST_NAME = 'John';
    private const VALID_LAST_NAME = 'Doe';
    private const INVALID_EMAIL = 'invalid-email';
    private const EXISTING_EMAIL = 'existing@example.com';

    private ClientRepository|MockObject $clientRepository;
    private EventBus|MockObject $eventBus;
    private RegisterClientCommandHandler $handler;

    protected function setUp(): void
    {
        $this->clientRepository = $this->createMock(ClientRepository::class);
        $this->eventBus = $this->createMock(EventBus::class);
        $this->handler = new RegisterClientCommandHandler(
            $this->clientRepository,
            $this->eventBus
        );
    }

    /**
     * @test
     * @group client-management
     * @group registration
     */
    public function should_register_client_when_data_is_valid(): void
    {
        // Arrange
        $command = new RegisterClientCommand(
            email: self::VALID_EMAIL,
            firstName: self::VALID_FIRST_NAME,
            lastName: self::VALID_LAST_NAME
        );

        $this->clientRepository
            ->expects($this->once())
            ->method('emailExist')
            ->with($this->isInstanceOf(Email::class))
            ->willReturn(false);

        $this->clientRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Client::class));

        $this->eventBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ClientHasBeenRegistered::class));

        // Act
        $result = $this->handler->__invoke($command);

        // Assert
        $this->assertInstanceOf(
            ClientDTO::class, 
            $result,
            'Handler should return a ClientDTO instance'
        );
        $this->assertSame(
            self::VALID_EMAIL,
            $result->email,
            'Client email should match the provided email'
        );
        $this->assertSame(
            self::VALID_FIRST_NAME,
            $result->firstName,
            'Client first name should match the provided first name'
        );
        $this->assertSame(
            self::VALID_LAST_NAME,
            $result->lastName,
            'Client last name should match the provided last name'
        );
    }

    /**
     * @test
     * @group client-management
     * @group registration
     * @group validation
     */
    public function should_throw_exception_when_email_already_exists(): void
    {
        // Arrange
        $command = new RegisterClientCommand(
            email: self::EXISTING_EMAIL,
            firstName: self::VALID_FIRST_NAME,
            lastName: self::VALID_LAST_NAME
        );

        $this->clientRepository
            ->expects($this->once())
            ->method('emailExist')
            ->willReturn(true);

        $this->expectException(EmailAlreadyUsed::class);
        $this->expectExceptionMessage(sprintf('Email "%s" is already used.', self::EXISTING_EMAIL));

        // Act & Assert
        $this->handler->__invoke($command);
    }

    /**
     * @test
     * @group client-management
     * @group registration
     * @group validation
     */
    public function should_throw_exception_when_email_format_is_invalid(): void
    {
        // Arrange
        $command = new RegisterClientCommand(
            email: self::INVALID_EMAIL,
            firstName: self::VALID_FIRST_NAME,
            lastName: self::VALID_LAST_NAME
        );

        $this->expectException(InvalidFormat::class);
        $this->expectExceptionMessage(sprintf('The email "%s" is not valid.', self::INVALID_EMAIL));

        // Act & Assert
        $this->handler->__invoke($command);
    }

    /**
     * @test
     * @group client-management
     * @group registration
     * @group validation
     */
    public function should_throw_exception_when_first_name_is_empty(): void
    {
        // Arrange
        $command = new RegisterClientCommand(
            email: self::VALID_EMAIL,
            firstName: '',
            lastName: self::VALID_LAST_NAME
        );

        $this->expectException(InvalidFormat::class);
        $this->expectExceptionMessage('First name cannot be empty');

        // Act & Assert
        $this->handler->__invoke($command);
    }

    /**
     * @test
     * @group client-management
     * @group registration
     * @group validation
     */
    public function should_throw_exception_when_last_name_is_empty(): void
    {
        // Arrange
        $command = new RegisterClientCommand(
            email: self::VALID_EMAIL,
            firstName: self::VALID_FIRST_NAME,
            lastName: ''
        );

        $this->expectException(InvalidFormat::class);
        $this->expectExceptionMessage('Last name cannot be empty');

        // Act & Assert
        $this->handler->__invoke($command);
    }
}