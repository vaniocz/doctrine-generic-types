<?php
namespace Vanio\DoctrineGenericTypes\Patches\ORM\Mapping;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

class DoctrineAnnotationsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    function test_proper_annotations_are_included()
    {
        $this->assertFalse(class_exists(Column::class, false));
        $this->assertFalse(class_exists(Entity::class, false));

        require __DIR__ . '/../../../../../src/Patches/ORM/Mapping/Driver/DoctrineAnnotations.php';
        require __DIR__ . '/../../../../../src/Patches/ORM/Mapping/Driver/DoctrineAnnotations.php';

        $this->assertTrue(class_exists(Column::class, false));
        $this->assertTrue(class_exists(Entity::class, false));
        $this->assertSame(
            realpath(__DIR__ . '/../../../../../src/Patches/ORM/Mapping/Column.php'),
            (new \ReflectionClass(Column::class))->getFileName()
        );
    }
}
