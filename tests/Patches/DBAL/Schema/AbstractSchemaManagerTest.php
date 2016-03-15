<?php
namespace Vanio\DoctrineGenericTypes\Patches\DBAL\Schema;

use Doctrine\DBAL\Schema\AbstractSchemaManager;

class AbstractSchemaManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractSchemaManager|\PHPUnit_Framework_MockObject_MockObject */
    private $schemaManager;

    protected function setUp()
    {
        $this->schemaManager = $this->getMockBuilder(AbstractSchemaManager::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    function test_patch_is_applied()
    {
        $this->assertStringEndsWith(
            'DoctrineDBALSchemaAbstractSchemaManager.php',
            (new \ReflectionClass(AbstractSchemaManager::class))->getFileName()
        );
    }

    function test_it_accepts_generic_types()
    {
        $type = $this->schemaManager->extractDoctrineTypeFromComment('(DC2Type:foo)', null);
        $this->assertSame('foo', $type);

        $type = $this->schemaManager->extractDoctrineTypeFromComment('(DC2Type:foo<int, string>)', null);
        $this->assertSame('foo<int, string>', $type);

        $type = $this->schemaManager->extractDoctrineTypeFromComment('(DC2Type:foo@', null);
        $this->assertNull($type);
    }
}
