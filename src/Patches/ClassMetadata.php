<?php
namespace Doctrine\ORM\Mapping;

/**
 * - Allows adding extra metadata attributes @see http://www.doctrine-project.org/jira/browse/DDC-3391
 * - Removes type default value (string), keeps null in that case so type guessing makes more sense.
 */
class ClassMetadata extends ClassMetadataInfo
{
    /** @var array */
    public $extra = [];

    public function __sleep(): array
    {
        $serialized = parent::__sleep();
        $serialized[] = 'extra';

        return $serialized;
    }

    /**
     * @param array $mapping
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
}
