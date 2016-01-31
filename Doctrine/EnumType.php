<?php

namespace Preemiere\CoreBundle\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;
use Preemiere\Enum;

/**
 * @author Konstantin Grachev <ko@grachev.io>
 */
abstract class EnumType extends IntegerType
{
    const ENUM = 'enum';

    protected $class;

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof Enum) {
            return $value->getId();
        }

        return $value;
    }

    public function getName()
    {
        return self::ENUM;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null !== $value) {
            $class = $this->getClass();
            $value = new $class($value);
        }

        return $value;
    }

    protected function getClass()
    {
        return $this->class;
    }
}
