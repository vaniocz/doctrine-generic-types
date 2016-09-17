<?php
namespace Vanio\DoctrineGenericTypes\Patches\ORM\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

class ClassMetadataTest extends TestCase
{
    function test_patch_is_applied()
    {
        $this->assertSame(
            realpath(__DIR__ . '/../../../../src/Patches/ORM/Mapping/ClassMetadata.php'),
            (new \ReflectionClass(ClassMetadata::class))->getFileName()
        );
    }

    function test_fields_have_no_default_type()
    {
        $classMetadata = new ClassMetadata('entity');

        $classMetadata->mapField(['fieldName' => 'foo']);
        $this->assertNull($classMetadata->getTypeOfField('foo'));

        $classMetadata->mapField(['fieldName' => 'bar', 'type' => 'string']);
        $this->assertSame('string', $classMetadata->getTypeOfField('bar'));
    }

    function test_storing_extra_metadata()
    {
        $classMetadata = new ClassMetadata('entity');
        $classMetadata->extra['foo'] = 'foo';
        $this->assertSame('foo', unserialize(serialize($classMetadata))->extra['foo']);
    }
}
