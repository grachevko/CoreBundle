<?php

namespace Preemiere\CoreBundle\Form;

use Preemiere\Enum;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

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
            if (!call_user_func([$this->class, 'hasId'], $enum)) {
                throw new TransformationFailedException(sprintf('An Enum class "%s" with number "%s" does not exist!', $this->class, $enum));
            }

            $enum = new $this->class($enum);
        }

        return $enum;
    }
}
