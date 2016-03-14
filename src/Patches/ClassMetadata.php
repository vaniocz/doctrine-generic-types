<?php
namespace Doctrine\ORM\Mapping;

class ClassMetadata extends ClassMetadataInfo
{
    /**
     * Removes type default value, keeps null in that case.
     *
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
