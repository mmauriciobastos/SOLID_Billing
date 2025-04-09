<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Entity;

use App\Authentication\Domain\Exception\NewPasswordShouldBeDifferentOfCurrentPassword;
use App\Authentication\Domain\ValueObject\HashedPassword;
use App\Authentication\Domain\ValueObject\Username;
use App\ClientManagement\Domain\ValueObject\ClientId;

class ClientCredential
{
    private function __construct(
        private readonly ClientId $clientId,
        private Username $username,
        private HashedPassword $hashedPassword,
    ) {
    }

    public static function create(
        ClientId $clientId,
        Username $username,
        HashedPassword $hashedPassword,
    ): ClientCredential {
        return new self($clientId, $username, $hashedPassword);
    }

    public function clientId(): ClientId
    {
        return $this->clientId;
    }

    public function username(): Username
    {
        return $this->username;
    }

    public function hashedPassword(): HashedPassword
    {
        return $this->hashedPassword;
    }

    public function changeUsername(Username $username): void
    {
        $this->username = $username;
    }

    /**
     * @throws NewPasswordShouldBeDifferentOfCurrentPassword
     */
    public function changePassword(HashedPassword $hashedPassword): void
    {
        if ($this->hashedPassword()->isEqual($hashedPassword)) {
            throw new NewPasswordShouldBeDifferentOfCurrentPassword();
        }

        $this->hashedPassword = $hashedPassword;
    }
}
