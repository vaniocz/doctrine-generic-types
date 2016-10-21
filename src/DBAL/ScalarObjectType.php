<?php
namespace Vanio\DoctrineGenericTypes\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class ScalarObjectType extends GenericType
{
    const NAME = 'scalar_object';

    public function name(): string
    {
        return self::NAME;
    }

    /**
     * @param ScalarObject|null $value
     * @param AbstractPlatform $platform
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value ? $value->scalarValue() : null;
    }

    /**
     * Converts a value from its database representation to its PHP representation
     * of this type.
     *
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return ScalarObject|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $class = $this->scalarObjectClass();

        return new $class($value);
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $class = $this->scalarObjectClass();

        switch ($class::scalarType()) {
            case ScalarObject::BOOLEAN:
                return $platform->getBooleanTypeDeclarationSQL($fieldDeclaration);
            case ScalarObject::INTEGER:
                return $platform->getIntegerTypeDeclarationSQL($fieldDeclaration);
            case ScalarObject::FLOAT:
                return $platform->getFloatDeclarationSQL($fieldDeclaration);
        }

        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    private function scalarObjectClass(): string
    {
        $typeParameters = $this->typeParameters();

        return reset($typeParameters);
    }
}
