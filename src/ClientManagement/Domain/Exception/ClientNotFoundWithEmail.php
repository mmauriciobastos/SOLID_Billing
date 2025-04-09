<?php

declare(strict_types=1);

namespace App\ClientManagement\Domain\Exception;

use App\Common\Domain\ValueObject\Email;

final class ClientNotFoundWithEmail extends ClientNotFound
{
    public function __construct(Email $email)
    {
        parent::__construct(sprintf('Client not found with email "%s".', (string) $email));
    }
}
