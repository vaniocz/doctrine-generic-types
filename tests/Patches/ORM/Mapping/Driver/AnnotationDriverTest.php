<?php
namespace Vanio\DoctrineGenericTypes\Patches\ORM\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use PHPUnit\Framework\TestCase;
use Vanio\DoctrineGenericTypes\Tests\Fixtures\Entity;

class AnnotationDriverTest extends TestCase
{
    function test_loading_metadata_for_class()
    {
        $metadata = new ClassMetadata(Entity::class);
        AnnotationDriver::create()->loadMetadataForClass(Entity::class, $metadata);
        $this->assertSame('string', $metadata->getSingleIdentifierFieldName());
    }
}
