<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Setono\ClientId\ClientId;

final class ClientIdType extends Type
{
    public const CLIENT_ID = 'client_id';

    public function getName(): string
    {
        return self::CLIENT_ID;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        /** @psalm-suppress DeprecatedMethod */
        return $platform->getVarcharTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ClientId
    {
        if ($value instanceof ClientId || null === $value) {
            return $value;
        }

        if (!\is_string($value)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'string', ClientId::class]);
        }

        return new ClientId($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof ClientId) {
            return $value->toString();
        }

        if (null === $value || '' === $value) {
            return null;
        }

        if (!\is_string($value)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'string', ClientId::class]);
        }

        return $value;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
