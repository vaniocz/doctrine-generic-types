<?php
namespace Vanio\DoctrineGenericTypes\Patches\ORM\Mapping;

use Doctrine\ORM\Mapping\Column;
use PHPUnit\Framework\TestCase;

class ColumnTest extends TestCase
{
    function test_patch_is_applied()
    {
        $this->assertSame(
            realpath(__DIR__ . '/../../../../src/Patches/ORM/Mapping/Column.php'),
            (new \ReflectionClass(Column::class))->getFileName()
        );
    }
}
