<?php

declare(strict_types=1);

namespace Tests\ClientManagement\Infrastructure\Event;

use App\Authentication\Domain\Event\ClientHasBeenRegistered;
use App\ClientManagement\Application\DTO\ClientDTO;
use App\ClientManagement\Application\UseCase\GetClientById\GetClientByIdQuery;
use App\ClientManagement\Domain\Service\EmailService;
use App\ClientManagement\Infrastructure\Event\SendWelcomeEmailWhenClientRegisteredSubscriber;
use App\Common\Application\Query\QueryBus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SendWelcomeEmailWhenClientRegisteredSubscriberTest extends TestCase
{
    private const CLIENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const CLIENT_EMAIL = 'john.doe@example.com';
    private const CLIENT_FIRST_NAME = 'John';
    private const CLIENT_LAST_NAME = 'Doe';

    private QueryBus|MockObject $queryBus;
    private EmailService|MockObject $emailService;
    private SendWelcomeEmailWhenClientRegisteredSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->queryBus = $this->createMock(QueryBus::class);
        $this->emailService = $this->createMock(EmailService::class);
        $this->subscriber = new SendWelcomeEmailWhenClientRegisteredSubscriber(
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
        $event = ClientHasBeenRegistered::withClientId(self::CLIENT_ID);
        $clientDTO = new ClientDTO(
            id: self::CLIENT_ID,
            firstName: self::CLIENT_FIRST_NAME,
            lastName: self::CLIENT_LAST_NAME,
            email: self::CLIENT_EMAIL
        );

        $this->queryBus
            ->expects($this->once())
            ->method('ask')
            ->with(new GetClientByIdQuery(self::CLIENT_ID))
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
        $event = ClientHasBeenRegistered::withClientId(self::CLIENT_ID);

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
        $subscribedEvents = SendWelcomeEmailWhenClientRegisteredSubscriber::subscribedTo();

        // Assert
        $this->assertContains(
            ClientHasBeenRegistered::class,
            $subscribedEvents,
            'Subscriber should listen to ClientHasBeenRegistered event'
        );
    }
}