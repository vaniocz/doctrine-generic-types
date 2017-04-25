<?php
namespace Vanio\DoctrineGenericTypes\Tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\PostgreSQL92Platform;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use Vanio\DoctrineGenericTypes\DBAL\UniversalJsonType;

class UniversalJsonTypeTest extends TestCase
{
    /** @var UniversalJsonType */
    private $universalJsonType;

    /** @var PostgreSQL92Platform */
    private $platform;

    public static function setUpBeforeClass()
    {
        Type::addType(UniversalJsonType::NAME, UniversalJsonType::class);
    }

    protected function setUp()
    {
        $this->universalJsonType = Type::getType(UniversalJsonType::NAME);
        $this->platform = new PostgreSQL92Platform;
    }

    function test_getting_name()
    {
        $this->assertSame('universal_json', $this->universalJsonType->getName());
    }

    function test_getting_sql_declaration()
    {
        $this->assertSame('JSON', $this->universalJsonType->getSQLDeclaration([], $this->platform));
    }

    function test_it_requires_sql_comment_hint()
    {
        $this->assertTrue($this->universalJsonType->requiresSQLCommentHint($this->platform));
    }

    function test_converting_to_database_value()
    {
        $this->assertNull($this->universalJsonType->convertToDatabaseValue(null, $this->platform));
        $this->assertSame('[]', $this->universalJsonType->convertToDatabaseValue([], $this->platform));
        $this->assertSame(
            '{"foo":"foo"}',
            $this->universalJsonType->convertToDatabaseValue(['foo' => 'foo'], $this->platform)
        );
    }

    function test_converting_to_php_value()
    {
        $this->assertNull($this->universalJsonType->convertToPHPValue(null, $this->platform));
        $this->assertSame(
            [],
            $this->universalJsonType->convertToPHPValue('[]', $this->platform)
        );
        $this->assertSame(
            ['foo' => 'foo'],
            $this->universalJsonType->convertToPHPValue('{"foo":"foo"}', $this->platform)
        );
    }

    /**
     * @dataProvider values
     * @param mixed $value
     */
    function test_converting_to_database_value_and_back($value)
    {
        $databaseValue = $this->universalJsonType->convertToDatabaseValue($value, $this->platform);
        $this->assertEquals(
            $value,
            $this->universalJsonType->convertToPHPValue($databaseValue, $this->platform)
        );
    }

    public function values(): array
    {
        return [
            [null],
            [['foo' => 'foo']],
            [new \stdClass],
            [new \DateTime],
            [DriverManager::getConnection(['driver' => 'pdo_mysql'])],
        ];
    }
}
