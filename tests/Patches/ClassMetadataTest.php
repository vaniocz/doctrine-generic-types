<?php
namespace Vanio\DoctrineGenericTypes\Tests\Patches;

use Doctrine\ORM\Mapping\ClassMetadata;

class ClassMetadataTest extends \PHPUnit_Framework_TestCase
{
    function test_patch_is_applied()
    {
        $this->assertSame(
            realpath(__DIR__ . '/../../src/Patches/ClassMetadata.php'),
            (new \ReflectionClass(ClassMetadata::class))->getFileName()
        );
    }

    function test_type_does_not_have_a_default_value()
    {
        $classMetadata = new ClassMetadata('entity');

        $classMetadata->mapField(['fieldName' => 'foo']);
        $this->assertNull($classMetadata->getTypeOfField('foo'));

        $classMetadata->mapField(['fieldName' => 'bar', 'type' => 'string']);
        $this->assertSame('string', $classMetadata->getTypeOfField('bar'));
    }
}
