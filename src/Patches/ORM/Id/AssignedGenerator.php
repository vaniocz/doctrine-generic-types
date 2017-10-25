<?php
namespace Doctrine\ORM\Id;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;

/**
 * Automatically populates discriminator field when it is part of ID.
 */
class AssignedGenerator extends AbstractIdGenerator
{
    /**
     * @param EntityManager $entityManager
     * @param mixed $entity
     * @throws ORMException
     * @return array
     */
    public function generate(EntityManager $entityManager, $entity): array
    {
        $classMetadata = $entityManager->getClassMetadata(get_class($entity));
        $id = [];

        foreach ($classMetadata->identifier as $field) {
            $value = $field === $classMetadata->identifierDiscriminatorField
                ? $classMetadata->discriminatorValue
                : $classMetadata->getFieldValue($entity, $field);

            if ($value === null) {
                throw ORMException::entityMissingAssignedIdForField($entity, $field);
            } elseif (isset($classMetadata->associationMappings[$field])) {
                $value = $entityManager->getUnitOfWork()->getSingleIdentifierValue($value);
            }

            $id[$field] = $value;
        }

        return $id;
    }
}
