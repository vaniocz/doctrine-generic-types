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

            if (!$typeGuess) {
                $type = Type::STRING;
            } elseif ($typeGuess->typeParameters()) {
                $type = sprintf('%s<%s>', $typeGuess->type(), implode(', ', $typeGuess->typeParameters()));
            } else {
                $type = $typeGuess->type();
            }

            $metadata->fieldMappings[$field]['type'] = $type;
            $metadata->fieldMappings[$field]['nullable'] = $metadata->getFieldMapping($field)['nullable'] ??
                ($typeGuess ? $typeGuess->isNullable() : false);
        }
    }

    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }
}
