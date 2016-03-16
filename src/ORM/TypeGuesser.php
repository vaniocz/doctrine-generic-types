<?php
namespace Vanio\DoctrineGenericTypes\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Mapping\ClassMetadata;

interface TypeGuesser
{
    /**
     * @param ClassMetadata $metadata
     * @param string $property
     * @param AbstractPlatform|null $platform
     * @return TypeGuess|null
     */
    public function guessType(ClassMetadata $metadata, string $property, AbstractPlatform $platform = null);
}
