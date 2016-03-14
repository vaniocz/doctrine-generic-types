<?php
namespace Vanio\DoctrineGenericTypes\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Mapping\ClassMetadata;
use Vanio\DoctrineGenericTypes\TypeGuess;

class TypeGuesserChain implements TypeGuesser
{
    /** @var TypeGuesser[] */
    private $typeGuessers;

    public function __construct(array $typeGuessers)
    {
        $this->typeGuessers = $typeGuessers;
    }

    /**
     * @param ClassMetadata $metadata
     * @param string $property
     * @param AbstractPlatform|null $platform
     * @return TypeGuess|null
     */
    public function guessType(ClassMetadata $metadata, string $property, AbstractPlatform $platform = null)
    {
        foreach ($this->typeGuessers as $typeGuesser) {
            if ($typeGuess = $typeGuesser->guessType($metadata, $property, $platform)) {
                return $typeGuess;
            }
        }

        return null;
    }
}
