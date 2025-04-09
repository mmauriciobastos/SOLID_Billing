<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Repository;

use App\Authentication\Domain\Entity\ClientCredential;
use App\Authentication\Domain\ValueObject\Username;

interface ClientCredentialRepository
{
    public function getByUsername(Username $username): ClientCredential;

    public function add(ClientCredential $clientCredential): void;
}
