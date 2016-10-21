<?php
namespace Vanio\DoctrineGenericTypes\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Mapping\ClassMetadata;
use Vanio\DoctrineGenericTypes\DBAL\ScalarObject;
use Vanio\DoctrineGenericTypes\DBAL\ScalarObjectType;
use Vanio\TypeParser\Parser;

class ScalarObjectTypeGuesser implements TypeGuesser
{
    /** @var Parser */
    private $typeParser;

    public function __construct(Parser $typeParser)
    {
        $this->typeParser = $typeParser;
    }

    /**
     * @param ClassMetadata $metadata
     * @param string $property
     * @param AbstractPlatform|null $platform
     * @return TypeGuess|null
     */
    public function guessType(ClassMetadata $metadata, string $property, AbstractPlatform $platform = null)
    {
        $type = $this->typeParser->parsePropertyTypes($metadata->name)[$property] ?? null;

        return $type && $type->isTypedObject() && is_a($type->type(), ScalarObject::class, true)
            ? new TypeGuess(ScalarObjectType::NAME, $type->isNullable(), [$type->type()])
            : null;
    }
}
