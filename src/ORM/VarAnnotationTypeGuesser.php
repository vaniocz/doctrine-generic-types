<?php
namespace Vanio\DoctrineGenericTypes\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Vanio\DoctrineGenericTypes\TypeGuess;
use Vanio\TypeParser\Parser;
use Vanio\TypeParser\Type as ParserType;

class VarAnnotationTypeGuesser implements TypeGuesser
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
        if (!$type = $this->typeParser->parsePropertyTypes($metadata->name)[$property] ?? null) {
            return null;
        } elseif ($type->type() === ParserType::OBJECT || $type->isTypedObject()) {
            return new TypeGuess(Type::OBJECT, $type->isNullable());
        } elseif ($type->type() === ParserType::INTEGER) {
            return new TypeGuess(Type::INTEGER, $type->isNullable());
        } elseif ($type->type() === ParserType::FLOAT) {
            return new TypeGuess(Type::FLOAT, $type->isNullable());
        }

        return null;
    }
}
