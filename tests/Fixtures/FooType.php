<?php
namespace Vanio\DoctrineGenericTypes\Tests\Fixtures;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class FooType extends Type
{
    public function getName(): string
    {
        return 'foo';
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return '';
    }
}
