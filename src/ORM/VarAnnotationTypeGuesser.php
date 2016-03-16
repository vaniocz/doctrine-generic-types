<?php
namespace Vanio\DoctrineGenericTypes\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Vanio\TypeParser\Parser;
use Vanio\TypeParser\Type as ParserType;

class VarAnnotationTypeGuesser implements TypeGuesser
{
    /** @var string[] */
    private static $types = [
        ParserType::STRING => Type::STRING,
        ParserType::INTEGER => Type::INTEGER,
        ParserType::BOOLEAN => Type::BOOLEAN,
        ParserType::FLOAT => Type::FLOAT,
        ParserType::SCALAR => Type::STRING,
        ParserType::ARRAY => Type::TARRAY,
    ];

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
        } elseif ($type->isGeneric() && $type->type() === ParserType::ARRAY) {
            return $this->guessTypedArray($type);
        } elseif ($type->isTypedObject()) {
            return is_a($type->type(), \DateTimeInterface::class, true)
                ? new TypeGuess(Type::DATETIME, $type->isNullable())
                : new TypeGuess(Type::OBJECT, $type->isNullable());
        } elseif ($typeName = self::$types[$type->type()] ?? null) {
            return new TypeGuess($typeName, $type->isNullable());
        }

        return new TypeGuess(Type::OBJECT, $type->isNullable());
    }

    private function guessTypedArray(ParserType $type): TypeGuess
    {
        foreach ($type->typeParameters() as $typeParameter) {
            if (!$typeParameter->isScalar()) {
                return new TypeGuess(Type::TARRAY);
            }
        }

        return new TypeGuess(Type::JSON_ARRAY, $type->isNullable());
    }
}
