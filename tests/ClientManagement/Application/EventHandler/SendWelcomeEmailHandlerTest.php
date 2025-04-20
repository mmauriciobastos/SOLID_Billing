<?php

declare(strict_types=1);

namespace Tests\ClientManagement\Application\EventHandler;

use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\EventHandler\SendWelcomeEmailHandler;
use App\ClientManagement\Application\UseCase\GetClientById\GetClientByIdQuery;
use App\ClientManagement\Domain\Entity\Client;
use App\ClientManagement\Domain\Event\ClientHasBeenRegisteredEvent;
use App\ClientManagement\Domain\Service\EmailService;
use App\Common\Application\Query\QueryBus;
use App\Common\Domain\ValueObject\Email;
use App\Common\Domain\ValueObject\FirstName;
use App\Common\Domain\ValueObject\LastName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SendWelcomeEmailHandlerTest extends TestCase
{
    private const CLIENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const CLIENT_EMAIL = 'john.doe@example.com';
    private const CLIENT_FIRST_NAME = 'John';
    private const CLIENT_LAST_NAME = 'Doe';

    private QueryBus|MockObject $queryBus;
    private EmailService|MockObject $emailService;
    private SendWelcomeEmailHandler $subscriber;

    protected function setUp(): void
    {
        $this->queryBus = $this->createMock(QueryBus::class);
        $this->emailService = $this->createMock(EmailService::class);
        $this->subscriber = new SendWelcomeEmailHandler(
            $this->emailService,
            $this->queryBus
        );
    }

    /**
     * @test
     * @group client-management
     * @group email
     */
    public function should_send_welcome_email_when_client_registered(): void
    {
        // Arrange
        $client = Client::create(
            FirstName::fromString(self::CLIENT_FIRST_NAME),
            LastName::fromString(self::CLIENT_LAST_NAME),
            Email::fromString(self::CLIENT_EMAIL)
        );
        $clientDTO = ClientDTO::fromEntity($client);
        $event = ClientHasBeenRegisteredEvent::withClientId($client->id()->value());

        $this->queryBus
            ->expects($this->once())
            ->method('ask')
            ->with(new GetClientByIdQuery($client->id()->value()))
            ->willReturn($clientDTO);

        $this->emailService
            ->expects($this->once())
            ->method('sendWelcomeEmail')
            ->with(self::CLIENT_EMAIL, self::CLIENT_FIRST_NAME);

        // Act
        $this->subscriber->__invoke($event);
    }

    /**
     * @test
     * @group client-management
     * @group email
     */
    public function should_not_send_welcome_email_when_client_not_found(): void
    {
        // Arrange
        $event = ClientHasBeenRegisteredEvent::withClientId(self::CLIENT_ID);

        $this->queryBus
            ->expects($this->once())
            ->method('ask')
            ->with(new GetClientByIdQuery(self::CLIENT_ID))
            ->willReturn(null);

        $this->emailService
            ->expects($this->never())
            ->method('sendWelcomeEmail');

        // Act
        $this->subscriber->__invoke($event);
    }

    /**
     * @test
     * @group client-management
     * @group email
     */
    public function should_subscribe_to_client_registered_event(): void
    {
        // Act
        $subscribedEvents = SendWelcomeEmailHandler::subscribedTo();

        // Assert
        $this->assertContains(
            ClientHasBeenRegisteredEvent::class,
            $subscribedEvents,
            'Subscriber should listen to ClientHasBeenRegisteredEvent event'
        );
    }
}