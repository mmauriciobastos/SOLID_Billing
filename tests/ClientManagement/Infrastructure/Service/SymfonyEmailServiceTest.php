<?php

declare(strict_types=1);

namespace Tests\ClientManagement\Infrastructure\Service;

use App\ClientManagement\Infrastructure\Service\SymfonyEmailService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class SymfonyEmailServiceTest extends TestCase
{
    private const FROM_EMAIL = 'no-reply@example.com';
    private const TO_EMAIL = 'john.doe@example.com';
    private const FIRST_NAME = 'John';

    private MailerInterface|MockObject $mailer;
    private LoggerInterface|MockObject $logger;
    private SymfonyEmailService $emailService;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->emailService = new SymfonyEmailService(
            $this->mailer,
            $this->logger,
            self::FROM_EMAIL
        );
    }

    /**
     * @test
     * @group client-management
     * @group email
     */
    public function should_send_welcome_email_successfully(): void
    {
        // Arrange
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                return $email->getFrom()[0]->getAddress() === self::FROM_EMAIL
                    && $email->getTo()[0]->getAddress() === self::TO_EMAIL
                    && str_contains($email->getHtmlBody(), self::FIRST_NAME)
                    && $email->getSubject() === 'Welcome to Our Service!';
            }));

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Welcome email sent'));

        // Act
        $this->emailService->sendWelcomeEmail(self::TO_EMAIL, self::FIRST_NAME);
    }

    /**
     * @test
     * @group client-management
     * @group email
     * @group error-handling
     */
    public function should_log_error_when_email_sending_fails(): void
    {
        // Arrange
        $exception = new \Exception('Email sending failed');
        
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Failed to send welcome email'));

        // Act
        $this->emailService->sendWelcomeEmail(self::TO_EMAIL, self::FIRST_NAME);
    }
}