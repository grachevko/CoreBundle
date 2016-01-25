<?php

namespace Preemiere\CoreBundle\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;
use Preemiere\Enum;

/**
 * @author Grachev Konstantin Olegovich <ko@grachev.io>
 */
class EnumType extends IntegerType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof Enum) {
            return $value->getId();
        }

        return $value;
    }
}