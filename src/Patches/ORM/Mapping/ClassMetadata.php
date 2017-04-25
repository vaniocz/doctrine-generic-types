<?php
namespace Doctrine\ORM\Mapping;

/**
 * - Allows adding extra metadata attributes @see http://www.doctrine-project.org/jira/browse/DDC-3391
 * - Removes type default value (string), keeps null in that case so type guessing makes more sense.
 * - Handles embedded nullable mappings.
 */
class ClassMetadata extends ClassMetadataInfo
{
    /** @var array */
    public $extra = [];

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

        return $serialized;
    }

    /**
     * @throws MappingException
     */
    protected function _validateAndCompleteFieldMapping(array &$mapping)
    {
        $type = $mapping['type'] ?? null;
        parent::_validateAndCompleteFieldMapping($mapping);

        if (!$type) {
            $mapping['type'] = null;
        }
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
}
