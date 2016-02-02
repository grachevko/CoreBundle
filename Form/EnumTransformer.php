<?php

namespace Grachev\CoreBundle\Form;

use Grachev\Enum;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @author Konstantin Grachev <ko@grachev.io>
 */
class EnumTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * Transforms an object (Enum) to a string (number).
     *
     * @param Enum|null $enum
     *
     * @return string
     */
    public function transform($enum)
    {
        if ($enum instanceof Enum) {
            $enum = $enum->getId();
        }

        return (string) $enum;
    }

    /**
     * Transforms a string (number) to an object (Enum).
     *
     * @param string $enum
     *
     * @return Enum|null
     *
     * @throws TransformationFailedException if object (Enum) not contain submitted id.
     */
    public function reverseTransform($enum)
    {
        if (null !== $enum) {
            try {
                $enum = new $this->class($enum);
            } catch (\Exception $e) {
                throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $enum;
    }
}
