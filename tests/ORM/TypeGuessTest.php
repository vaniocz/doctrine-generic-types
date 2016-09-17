<?php
namespace Vanio\DoctrineGenericTypes\Tests;

use PHPUnit\Framework\TestCase;
use Vanio\DoctrineGenericTypes\ORM\TypeGuess;

class TypeGuessTest extends TestCase
{
    function test_type_can_be_obtained()
    {
        $this->assertSame('type', (new TypeGuess('type'))->type());
    }

    function test_it_can_be_nullable()
    {
        $this->assertTrue((new TypeGuess('type', true))->isNullable());
    }

    function test_it_does_not_have_to_be_nullable()
    {
        $this->assertFalse((new TypeGuess('type'))->isNullable());
    }
}
