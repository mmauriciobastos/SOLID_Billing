<?php

declare(strict_types=1);

namespace App\ClientManagement\Infrastructure\Doctrine\Type;

use App\ClientManagement\Domain\ValueObject\ClientStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Webmozart\Assert\Assert;

class ClientStatusType extends StringType
{
    public const TYPE = 'client_status';

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ClientStatus
    {
        if (null === $value) {
            return null;
        }

        return ClientStatus::tryFrom((string) $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        Assert::isInstanceOf($value, ClientStatus::class);
        /** @var ClientStatus $clientStatus */
        $clientStatus = $value;

        return $clientStatus->value;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getName(): string
    {
        return self::TYPE;
    }
}
