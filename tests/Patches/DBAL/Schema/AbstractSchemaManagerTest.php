<?php
namespace Vanio\DoctrineGenericTypes\Patches\DBAL\Schema;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\TestCase;

class AbstractSchemaManagerTest extends TestCase
{
    /** @var AbstractSchemaManager|\PHPUnit_Framework_MockObject_MockObject */
    private $schemaManager;

    protected function setUp()
    {
        $this->schemaManager = $this->getMockBuilder(AbstractSchemaManager::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    function test_extracting_type_from_comment()
    {
        $type = $this->schemaManager->extractDoctrineTypeFromComment('(DC2Type:type)', null);
        $this->assertSame('type', $type);

        $type = $this->schemaManager->extractDoctrineTypeFromComment('(DC2Type:type<string>)', null);
        $this->assertSame('type<string>', $type);

        $type = $this->schemaManager->extractDoctrineTypeFromComment('(DC2Type:type<int, string>)', null);
        $this->assertSame('type<int, string>', $type);

        $type = $this->schemaManager->extractDoctrineTypeFromComment('(DC2Type:type<int, string[]>)', null);
        $this->assertSame('type<int, string[]>', $type);

        $type = $this->schemaManager->extractDoctrineTypeFromComment('(DC2Type:type<int, \stdClass>)', null);
        $this->assertSame('type<int, \stdClass>', $type);

        $type = $this->schemaManager->extractDoctrineTypeFromComment('(DC2Type:malformed/)', null);
        $this->assertNull($type);
    }
}
