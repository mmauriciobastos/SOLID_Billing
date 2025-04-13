<?php

declare(strict_types=1);

namespace App\ClientManagement\Domain\Entity;

use App\Common\Domain\Entity\AggregateRoot;
use App\Common\Domain\ValueObject\DateTime;
use App\Common\Domain\ValueObject\Email;
use App\Common\Domain\ValueObject\FirstName;
use App\Common\Domain\ValueObject\LastName;
use App\ClientManagement\Domain\ValueObject\ClientId;

class Client extends AggregateRoot
{
    private readonly ClientId $id;

    private DateTime $createdAt;

    private ?DateTime $removedAt = null;

    private function __construct(
        private FirstName $firstName,
        private LastName $lastName,
        private Email $email,
    ) {
        $this->id = ClientId::generate();
        $this->createdAt = DateTime::now();
    }

    public static function create(
        FirstName $firstName,
        LastName $lastName,
        Email $email,
    ): Client {
        return new self(
            $firstName,
            $lastName,
            $email,
        );
    }

    public function id(): ClientId
    {
        return $this->id;
    }

    public function firstName(): FirstName
    {
        return $this->firstName;
    }

    public function lastName(): LastName
    {
        return $this->lastName;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function isRemoved(): bool
    {
        return null !== $this->removedAt;
    }

    public function remove(): void
    {
        $this->ensureIsNotAlreadyRemoved();

        $this->removedAt = DateTime::now();
    }

    private function ensureIsNotAlreadyRemoved(): void
    {
        if ($this->isRemoved()) {
            throw new \RuntimeException('Client is already removed !');
        }
    }

    public function updateProfile(
        FirstName $firstName,
        LastName $lastName,
    ): void {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
}
