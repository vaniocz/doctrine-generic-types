<?php
namespace Vanio\DoctrineGenericTypes\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Vanio\Stdlib\UniversalJsonDeserializer;
use Vanio\Stdlib\UniversalJsonSerializer;

class UniversalJsonType extends Type
{
    const NAME = 'universal_json';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value === null
            ? null
            : UniversalJsonDeserializer::deserialize(is_resource($value) ? stream_get_contents($value) : $value);
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value === null ? null : UniversalJsonSerializer::serialize($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
