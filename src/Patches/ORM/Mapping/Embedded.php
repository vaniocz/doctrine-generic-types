<?php
namespace Doctrine\ORM\Mapping;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Embedded implements Annotation
{
    /**
     * @Required
     * @var string
     */
    public $class;

    /** @var mixed */
    public $columnPrefix;

    /** @var bool */
    public $nullable;
}
