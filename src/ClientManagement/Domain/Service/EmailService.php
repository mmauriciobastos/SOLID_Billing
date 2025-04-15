<?php

declare(strict_types=1);

namespace App\ClientManagement\Domain\Service;

interface EmailService
{
    public function sendWelcomeEmail(string $to, string $firstName): void;
}