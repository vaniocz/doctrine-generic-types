<?php
namespace Vanio\DoctrineGenericTypes\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Vanio\DoctrineGenericTypes\DBAL\UniversalJsonType;
use Vanio\TypeParser\Parser;
use Vanio\TypeParser\Type as ParserType;

class VarAnnotationTypeGuesser implements TypeGuesser
{
    /** @var Parser */
    private $typeParser;

    /** @var string[] */
    private $types = [
        ParserType::STRING => Type::STRING,
        ParserType::INTEGER => Type::INTEGER,
        ParserType::BOOLEAN => Type::BOOLEAN,
        ParserType::FLOAT => Type::FLOAT,
        ParserType::SCALAR => Type::STRING,
    ];

    /** @var string[] */
    private $classes = [
        \DateTimeImmutable::class => 'datetime_immutable',
        \DateTimeInterface::class => Type::DATETIME,
        'Ramsey\Uuid\UuidInterface' => 'uuid',
    ];

    public function __construct(Parser $typeParser)
    {
        $this->typeParser = $typeParser;
    }

    public function registerType(string $parserType, string $doctrineType)
    {
        $this->types[$parserType] = $doctrineType;
    }

    public function registerClass(string $class, string $doctrineType)
    {
        $this->classes[$class] = $doctrineType;
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
            foreach ($this->classes as $class => $doctrineType) {
                if (Type::hasType($doctrineType) && ($type->type() === $class || is_a($type->type(), $class, true))) {
                    return new TypeGuess($doctrineType, $type->isNullable());
                }
            }
        } elseif ($doctrineType = $this->types[$type->type()] ?? null) {
            return new TypeGuess($doctrineType, $type->isNullable());
        }

        return new TypeGuess(UniversalJsonType::NAME, $type->isNullable());
    }

    private function guessTypedArray(ParserType $type): TypeGuess
    {
        foreach ($type->typeParameters() as $typeParameter) {
            if (!$typeParameter->isScalar()) {
                return new TypeGuess(UniversalJsonType::NAME, $type->isNullable());
            }
        }

        return new TypeGuess(Type::JSON_ARRAY, $type->isNullable());
    }
}
