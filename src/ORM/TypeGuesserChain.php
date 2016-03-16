<?php
namespace Vanio\DoctrineGenericTypes\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Mapping\ClassMetadata;

class TypeGuesserChain implements TypeGuesser
{
    /** @var TypeGuesser[] */
    private $typeGuessers;

    /**
     * @param TypeGuesser[] ...$typeGuessers
     */
    public function __construct(TypeGuesser ...$typeGuessers)
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
