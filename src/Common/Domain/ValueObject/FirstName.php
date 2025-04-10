<?php

declare(strict_types=1);

namespace App\Common\Domain\ValueObject;

use App\Common\Domain\Exception\InvalidFormat;

final class FirstName extends StringValue
{
    protected function __construct(string $value)
    {
        parent::__construct($value);
        $this->ensureIsNotEmpty();
    }

    private function ensureIsNotEmpty(): void
    {
        if (trim($this->value) === '') {
            throw new InvalidFormat('First name cannot be empty');
        }
    }
}
