<?php

namespace LearnToWin\GeneralBundle\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Override datetime datatype to support microseconds.
 *
 * @todo: Replace with package at some point.
 */
class DateTimeMicrosecondsType extends Type
{
    final public const TYPENAME = 'datetimems';
    private const DATETIME_FORMAT = 'Y-m-d H:i:s.u';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if (isset($column['version']) && $column['version']) {
            return 'TIMESTAMP';
        }

        return 'DATETIME(6)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?\DateTimeInterface
    {
        if (null === $value || $value instanceof \DateTimeInterface) {
            return $value;
        }

        $val = \DateTime::createFromFormat(self::DATETIME_FORMAT, $value);

        if (!$val) {
            $val = date_create($value);
        }

        if (!$val) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                self::DATETIME_FORMAT
            );
        }

        return $val;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(self::DATETIME_FORMAT);
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTime']);
    }

    public function getName(): string
    {
        return self::TYPENAME;
    }
}
