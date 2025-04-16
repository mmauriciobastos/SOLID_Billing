<?php

declare(strict_types=1);

namespace App\ClientManagement\Infrastructure\Service;

use App\ClientManagement\Domain\Service\EmailService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class SymfonyEmailService implements EmailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $senderEmail,
    ) {
    }

    public function sendWelcomeEmail(string $to, string $firstName): void
    {
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($to)
            ->subject('Welcome to Our Service!')
            ->html($this->createWelcomeEmailContent($firstName));

        try {
            $this->mailer->send($email);
            $this->logger->info(sprintf('Welcome email sent to %s (%s)', $firstName, $to));
        } catch (\Exception $exception) {
            $this->logger->error(sprintf(
                'Failed to send welcome email to %s (%s): %s',
                $firstName,
                $to,
                $exception->getMessage()
            ));
        }
    }

    private function createWelcomeEmailContent(string $firstName): string
    {
        return sprintf(
            '<h1>Welcome, %s!</h1>
                <p>Thank you for registering with our service. We are excited to have you on board!</p>
                <p>Best regards,<br>The Team</p>',
            htmlspecialchars($firstName)
        );
    }
}