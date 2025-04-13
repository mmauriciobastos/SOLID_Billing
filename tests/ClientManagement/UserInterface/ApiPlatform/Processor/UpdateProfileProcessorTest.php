<?php

declare(strict_types=1);

namespace Tests\ClientManagement\UserInterface\ApiPlatform\Processor;

use ApiPlatform\Metadata\Operation;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\UseCase\UpdateProfile\UpdateProfileCommand;
use App\ClientManagement\Domain\Entity\Client;
use App\ClientManagement\Domain\Exception\ClientNotFound;
use App\ClientManagement\Domain\Exception\ClientNotFoundWithId;
use App\ClientManagement\Domain\Exception\EmailAlreadyUsed;
use App\ClientManagement\Domain\Exception\DifferentEmailProvided;
use App\ClientManagement\Domain\ValueObject\ClientId;
use App\ClientManagement\UserInterface\ApiPlatform\Processor\UpdateProfileProcessor;
use App\ClientManagement\UserInterface\ApiPlatform\Resource\ClientResource;
use App\Common\Application\Command\CommandBus;
use App\Common\Domain\Exception\InvalidFormat;
use App\Common\Domain\ValueObject\FirstName;
use App\Common\Domain\ValueObject\LastName;
use App\Common\Domain\ValueObject\Email;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateProfileProcessorTest extends TestCase
{
    private const VALID_CLIENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const VALID_FIRST_NAME = 'John';
    private const VALID_LAST_NAME = 'Doe';
    private const VALID_EMAIL = 'john.doe@example.com';
    private const NEW_VALID_EMAIL = 'john.new@example.com';
    private const INVALID_CLIENT_ID = '550e8400-e29b-41d4-a716-446655440999';
    private const EXISTING_EMAIL = 'existing@example.com';

    private CommandBus|MockObject $commandBus;
    private UpdateProfileProcessor $processor;
    private Operation $operation;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->processor = new UpdateProfileProcessor($this->commandBus);
        $this->operation = $this->createMock(Operation::class);
    }

    /**
     * @test
     * @group client-management
     * @group api
     */
    public function should_update_client_profile_when_data_is_valid(): void
    {
        // Arrange
        $fakeClient = Client::create(
            FirstName::fromString(self::VALID_FIRST_NAME),
            LastName::fromString(self::VALID_LAST_NAME),
            Email::fromString(self::VALID_EMAIL)
        );

        $expectedDTO = ClientDTO::fromEntity($fakeClient);

        $clientResource = ClientResource::fromClientDTO($expectedDTO);

        $this->commandBus
            ->method('dispatch')
            ->with(new UpdateProfileCommand(
                clientId: $clientResource->id,
                firstName: $clientResource->firstName,
                lastName: $clientResource->lastName,
                email: $clientResource->email
            ))
            ->willReturn($expectedDTO);

        // Act
        $result = $this->processor->process(
            $clientResource,
            $this->operation,
            ['id' => $fakeClient->id()->value()]
        );

        // Assert
        $this->assertInstanceOf(
            ClientResource::class,
            $result,
            'Processor should return a ClientResource instance'
        );
        $this->assertSame(
            $fakeClient->id()->value(),
            $result->id,
            'Updated client should maintain the same ID'
        );
        $this->assertSame(
            self::VALID_FIRST_NAME,
            $result->firstName,
            'First name should be updated'
        );
        $this->assertSame(
            self::VALID_LAST_NAME,
            $result->lastName,
            'Last name should be updated'
        );
        $this->assertSame(
            self::VALID_EMAIL,
            $result->email,
            'Email should be updated'
        );
    }

    /**
     * @test
     * @group client-management
     * @group api
     * @group validation
     */
    public function should_throw_exception_when_client_not_found(): void
    {
        // Arrange
        $clientResource = new ClientResource(
            id: self::INVALID_CLIENT_ID,
            firstName: self::VALID_FIRST_NAME,
            lastName: self::VALID_LAST_NAME,
            email: self::VALID_EMAIL
        );

        $this->commandBus
            ->method('dispatch')
            ->willThrowException(new ClientNotFoundWithId(ClientId::fromString(self::INVALID_CLIENT_ID)));

        $this->expectException(NotFoundHttpException::class);

        // Act & Assert
        $this->processor->process(
            $clientResource,
            $this->operation,
            ['id' => self::INVALID_CLIENT_ID]
        );
    }

    /**
     * @test
     * @group client-management
     * @group api
     * @group validation
     */
    public function should_throw_exception_when_different_email_is_provided(): void
    {
        // Arrange
        $clientResource = new ClientResource(
            id: self::VALID_CLIENT_ID,
            firstName: self::VALID_FIRST_NAME,
            lastName: self::VALID_LAST_NAME,
            email: self::NEW_VALID_EMAIL
        );

        $this->commandBus
            ->method('dispatch')
            ->willThrowException(new DifferentEmailProvided());

        $this->expectException(BadRequestException::class);

        // Act & Assert
        $this->processor->process(
            $clientResource,
            $this->operation,
            ['id' => self::VALID_CLIENT_ID]
        );
    }

    /**
     * @test
     * @group client-management
     * @group api
     * @group validation
     */
    public function should_throw_exception_when_input_data_is_invalid(): void
    {
        // Arrange
        $this->expectException(\Webmozart\Assert\InvalidArgumentException::class);

        // Act & Assert
        $this->processor->process(
            'invalid_data',
            $this->operation,
            ['id' => self::VALID_CLIENT_ID]
        );
    }
}