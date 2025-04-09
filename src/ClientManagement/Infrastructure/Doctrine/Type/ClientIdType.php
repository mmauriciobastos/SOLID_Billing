<?php

declare(strict_types=1);

namespace App\ClientManagement\Infrastructure\Doctrine\Type;

use App\Common\Infrastructure\Doctrine\Type\UuidType;
use App\ClientManagement\Domain\ValueObject\ClientId;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class ClientIdType extends UuidType
{
    public const TYPE = 'client_id';

    public function convertToPHPValue($value, AbstractPlatform $platform): ClientId
    {
        return ClientId::fromString((string) $value);
    }
}
