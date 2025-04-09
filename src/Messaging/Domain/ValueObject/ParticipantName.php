<?php

declare(strict_types=1);

namespace App\Messaging\Domain\ValueObject;

use App\Common\Domain\ValueObject\StringValue;
use App\ClientManagement\Application\DTO\ClientDTO;

final class ParticipantName extends StringValue
{
    public static function fromClientDTO(ClientDTO $clientDTO): ParticipantName
    {
        return self::fromString($clientDTO->firstName . ' ' . $clientDTO->lastName);
    }
}
