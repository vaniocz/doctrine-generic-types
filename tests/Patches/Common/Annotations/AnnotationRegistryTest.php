<?php
namespace Vanio\DoctrineGenericTypes\Patches\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

class AnnotationRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    function test_file_registration()
    {
        $this->assertFalse(class_exists(Column::class, false));
        $this->assertFalse(class_exists(Entity::class, false));

        $directory = dirname((new \ReflectionClass(Entity::class))->getFileName());
        AnnotationRegistry::registerFile($directory . '/Driver/DoctrineAnnotations.php');
        AnnotationRegistry::registerFile($directory . '/Driver/DoctrineAnnotations.php');
        AnnotationRegistry::registerFile($directory . '/Column.php');
        AnnotationRegistry::registerFile($directory . '/Column.php');
        AnnotationRegistry::registerFile($directory . '/Entity.php');
        AnnotationRegistry::registerFile($directory . '/Entity.php');

        $this->assertTrue(class_exists(Column::class, false));
        $this->assertTrue(class_exists(Entity::class, false));
        $this->assertSame(
            realpath(__DIR__ . '/../../../../src/Patches/ORM/Mapping/Column.php'),
            (new \ReflectionClass(Column::class))->getFileName()
        );
    }
}
