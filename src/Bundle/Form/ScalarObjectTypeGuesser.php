<?php
namespace Vanio\DoctrineGenericTypes\Bundle\Form;

use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\TypeGuess;
use Vanio\DoctrineGenericTypes\DBAL\ScalarObject;
use Vanio\TypeParser\Parser;

class ScalarObjectTypeGuesser implements FormTypeGuesserInterface
{
    /** @var Parser */
    private $typeParser;

    public function __construct(Parser $typeParser)
    {
        $this->typeParser = $typeParser;
    }

    /**
     * @param string $class
     * @param string $property
     * @return TypeGuess|null
     */
    public function guessType($class, $property)
    {
        $type = $this->typeParser->parsePropertyTypes($class)[$property] ?? null;

        return $type && $type->isTypedObject() && is_a($type->type(), ScalarObject::class, true)
            ? new TypeGuess(ScalarObjectType::class, ['data_class' => $type->type()], TypeGuess::HIGH_CONFIDENCE)
            : null;
    }

    /**
     * @param string $class
     * @param string $property
     * @return null
     */
    public function guessRequired($class, $property)
    {
        return null;
    }

    /**
     * @param string $class
     * @param string $property
     * @return null
     */
    public function guessMaxLength($class, $property)
    {
        return null;
    }

    /**
     * @param string $class
     * @param string $property
     * @return null
     */
    public function guessPattern($class, $property)
    {
        return null;
    }
}
