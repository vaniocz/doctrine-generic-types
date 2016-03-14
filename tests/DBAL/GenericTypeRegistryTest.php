<?php
namespace Vanio\DoctrineGenericTypes\Tests\DBAL;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use Vanio\DoctrineGenericTypes\DBAL\GenericType;
use Vanio\DoctrineGenericTypes\DBAL\GenericTypeRegistry;
use Vanio\DoctrineGenericTypes\Tests\Fixtures\BarType;
use Vanio\DoctrineGenericTypes\Tests\Fixtures\FooType;

class GenericTypeRegistryTest extends \PHPUnit_Framework_TestCase
{
    /** @var GenericTypeRegistry */
    private $typeRegistry;

    protected function setUp()
    {
        $this->typeRegistry = new GenericTypeRegistry;
    }

    function test_registration()
    {
        Type::addType('foo', FooType::class);
        $type = Type::getType('foo');
        $this->typeRegistry->register();
        $this->assertSame($type, Type::getType('foo'));
        $this->typeRegistry->register();
        $this->assertSame($type, Type::getType('foo'));
    }

    function test_type_registration()
    {
        $this->register();

        $this->assertFalse(Type::hasType('foo'));
        Type::addType('foo', FooType::class);
        $this->assertTrue(Type::hasType('foo'));
        Type::overrideType('foo', null);
    }

    function test_type_unregistration()
    {
        $this->register();

        Type::addType('foo', FooType::class);
        $this->assertTrue(Type::hasType('foo'));
        $this->typeRegistry->removeType('foo');
        $this->assertFalse(Type::hasType('foo'));
    }

    function test_cannot_obtain_non_existent_type()
    {
        $this->register();

        $this->expectException(DBALException::class);
        $this->expectExceptionMessage('Unknown column type "foo" requested.');
        Type::getType('foo');
    }

    function test_type_can_be_obtained()
    {
        $this->register();

        $type = Type::getType(Type::INTEGER);
        $this->assertInstanceOf(Type::class, $type);
        $this->assertSame($type, Type::getType(Type::INTEGER));
    }

    function test_generic_type_can_be_obtained()
    {
        $this->register();

        Type::addType('bar', BarType::class);

        /** @var GenericType $type */
        $type = Type::getType('bar');
        $this->assertInstanceOf(BarType::class, $type);
        $this->assertSame([], $type->typeParameters());

        /** @var GenericType $type */
        $type = Type::getType('bar<int,string >');
        $this->assertInstanceOf(BarType::class, $type);
        $this->assertSame(['int', 'string'], $type->typeParameters());

        /** @var GenericType $type */
        $type = Type::getType('bar<int, string>');
        $this->assertInstanceOf(BarType::class, $type);
        $this->assertSame(['int', 'string'], $type->typeParameters());
    }

    function test_registration_of_type_instance()
    {
        $this->register();

        $this->assertFalse(Type::hasType('bar'));
        $this->typeRegistry->addType(BarType::create());
        $this->assertTrue(Type::hasType('bar'));
    }

    function test_type_unregistration_using_array_access()
    {
        $this->register();

        Type::addType('foo', FooType::class);
        $this->assertTrue(Type::hasType('foo'));
        unset($this->typeRegistry['foo']);
        $this->assertFalse(Type::hasType('foo'));
    }

    function test_type_can_be_obtained_using_array_access()
    {
        $this->register();

        $this->assertInstanceOf(Type::class, $this->typeRegistry[Type::INTEGER]);
    }

    function test_registration_of_type_instance_using_array_access()
    {
        $this->register();

        $this->assertFalse(Type::hasType('bar'));
        $this->typeRegistry['bar'] = BarType::create();
        $this->assertTrue(Type::hasType('bar'));
    }

    private function register()
    {
        $this->typeRegistry->register();
        $this->typeRegistry->removeType('foo');
        $this->typeRegistry->removeType('bar');
    }
}
