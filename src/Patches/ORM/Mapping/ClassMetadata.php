<?php
namespace Doctrine\ORM\Mapping;

/**
 * - Allows adding extra metadata attributes @see http://www.doctrine-project.org/jira/browse/DDC-3391
 * - Removes type default value (string), keeps null in that case so type guessing makes more sense.
 * - Handles embedded nullable mappings.
 * - Allows to map discriminator fields
 */
class ClassMetadata extends ClassMetadataInfo
{
    /** @var array */
    public $extra = [];

    /** @var string|null */
    public $identifierDiscriminatorField;

    public function setIdentifier(array $identifier)
    {
        parent::setIdentifier($identifier);
        $this->assignIdentifierDiscriminatorField();
    }

    public function mapEmbedded(array $mapping)
    {
        parent::mapEmbedded($mapping);
        $this->embeddedClasses[$mapping['fieldName']]['nullable'] = $mapping['nullable'] ?? null;
    }

    /**
     * @param string $property
     * @param ClassMetadataInfo $embeddable
     */
    public function inlineEmbeddable($property, ClassMetadataInfo $embeddable)
    {
        parent::inlineEmbeddable($property, $embeddable);

        foreach ($embeddable->fieldMappings as $fieldMapping) {
            if ($this->embeddedClasses[$property]['nullable'] === true) {
                $this->fieldMappings["$property.$fieldMapping[fieldName]"]['nullable'] = true;
            }
        }
    }

    /**
     * @param object $entity
     * @param array $data
     */
    public function populateEntity($entity, array $data)
    {
        foreach ($data as $field => $value) {
            if ($this->shouldPopulateField($field, $value)) {
                $this->reflFields[$field]->setValue($entity, $value);
            }
        }
    }

    public function __sleep(): array
    {
        $serialized = parent::__sleep();
        $serialized[] = 'extra';
        $serialized[] = 'identifierDiscriminatorField';

        return $serialized;
    }

    protected function _validateAndCompleteFieldMapping(array &$mapping)
    {
        $type = $mapping['type'] ?? null;
        $discriminatorColumn = $this->discriminatorColumn;
        $this->discriminatorColumn = null;
        parent::_validateAndCompleteFieldMapping($mapping);
        $this->discriminatorColumn = $discriminatorColumn;
        $this->assignIdentifierDiscriminatorField();

        if (!$type) {
            $mapping['type'] = null;
        }
    }

    protected function _validateAndCompleteAssociationMapping(array $mapping): array
    {
        $mapping = parent::_validateAndCompleteAssociationMapping($mapping);
        $this->assignIdentifierDiscriminatorField();

        return $mapping;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    private function shouldPopulateField(string $field, $value): bool
    {
        if (!isset($this->fieldMappings[$field])) {
            return false;
        } elseif ($value !== null) {
            return true;
        } elseif (!$property = $this->fieldMappings[$field]['declaredField'] ?? null) {
            return false;
        }

        return ($this->embeddedClasses[$property]['nullable'] ?? false) !== true;
    }

    private function assignIdentifierDiscriminatorField()
    {
        if (
            $this->identifierDiscriminatorField === null
            && isset($this->discriminatorColumn['fieldName'])
            && $this->isIdentifierComposite
            && in_array($this->discriminatorColumn['fieldName'], $this->identifier)
        ) {
            $this->identifierDiscriminatorField = $this->discriminatorColumn['fieldName'];
        }
    }
}
