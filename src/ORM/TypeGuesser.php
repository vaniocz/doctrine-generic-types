<?php
namespace Vanio\DoctrineGenericTypes;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Vanio\TypeParser\Parser;
use Vanio\TypeParser\Type as ParserType;

class TypeGuesser implements EventSubscriber
{
    /** @var Parser */
    private $typeParser;

    public function __construct(Parser $phpParser)
    {
        $this->typeParser = $phpParser;
    }

    /**
     * @param ClassMetadata $metadata
     * @param string $property
     * @return array An array where first value is type as a string
     *               and a second value is a boolean whether it's nullable
     */
    public function guessType(ClassMetadata $metadata, string $property): array
    {
        if (!$type = $this->typeParser->parsePropertyTypes($metadata->name)[$property] ?? null) {
            return [Type::STRING, false];
        } elseif ($type->isGeneric()) {
            foreach ($type->typeParameters() as $typeParameter) {
                if (!$typeParameter->isScalar()) {
                    return [Type::OBJECT, $type->isNullable()];
                }
            }

            return [Type::JSON_ARRAY, $type->isNullable()];
        } elseif ($type->type() === ParserType::OBJECT || $type->isTypedObject()) {
            return [Type::OBJECT, $type->isNullable()];
        } elseif ($type->type() === ParserType::INTEGER) {
            return [Type::INTEGER, $type->isNullable()];
        }

        return [Type::STRING, $type->isNullable()];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $event->getClassMetadata();

        foreach ($metadata->fieldNames as $field) {
            if ($metadata->getTypeOfField($field) === null) {
                list($type, $nullable) = $this->guessType($metadata, $field);
                $metadata->fieldMappings[$field]['type'] = $type;
                $metadata->fieldMappings[$field]['nullable'] = $nullable;
            }
        }
    }

    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }
}
