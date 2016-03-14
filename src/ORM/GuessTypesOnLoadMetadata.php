<?php
namespace Vanio\DoctrineGenericTypes\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

class GuessTypesOnLoadMetadata implements EventSubscriber
{
    /** @var TypeGuesser */
    private $typeGuesser;

    public function __construct(TypeGuesser $typeGuesser)
    {
        $this->typeGuesser = $typeGuesser;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $event->getClassMetadata();

        foreach ($metadata->fieldNames as $field) {
            if ($metadata->getTypeOfField($field)) {
                continue;
            }

            $connection = $event->getEntityManager()->getConnection();
            $typeGuess = $this->typeGuesser->guessType($metadata, $field, $connection->getDatabasePlatform());
            $metadata->fieldMappings[$field]['type'] = $typeGuess ? $typeGuess->type() : Type::STRING;
            $metadata->fieldMappings[$field]['nullable'] = $typeGuess ? $typeGuess->isNullable() : false;
        }
    }

    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }
}
