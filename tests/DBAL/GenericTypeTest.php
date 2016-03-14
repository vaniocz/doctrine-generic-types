<?php
namespace Vanio\DoctrineGenericTypes\Tests;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Vanio\DoctrineGenericTypes\Tests\Fixtures\BarType;

class GenericTypeTest extends \PHPUnit_Framework_TestCase
{
    function test_it_can_be_instantiated()
    {
        $this->assertInstanceOf(BarType::class, BarType::create());
    }

    function test_name_can_be_obtained()
    {
        $this->assertSame('bar', BarType::create()->getName());
        $this->assertSame('bar<string>', BarType::create('string')->getName());
        $this->assertSame('bar<int, string>', BarType::create('int', 'string')->getName());
    }

    function test_type_parameters_can_be_obtained()
    {
        $this->assertSame([], BarType::create()->typeParameters());
        $this->assertSame(['string'], BarType::create('string')->typeParameters());
        $this->assertSame(['int', 'string'], BarType::create('int', 'string')->typeParameters());
    }

    function test_type_parameters_are_normalized()
    {
        $this->assertSame([], BarType::create()->typeParameters());
        $this->assertSame(['string'], BarType::create('STRING')->typeParameters());
        $this->assertSame(['int', 'float'], BarType::create('integer', 'DOUBLE')->typeParameters());
    }

    function test_it_requires_sql_comment_hint()
    {
        $platform = $this->getMock(AbstractPlatform::class);
        /** @var AbstractPlatform|\PHPUnit_Framework_MockObject_MockObject $platform */
        $this->assertTrue(BarType::create()->requiresSQLCommentHint($platform));
    }
}
