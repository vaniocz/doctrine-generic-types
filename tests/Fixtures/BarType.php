<?php
namespace Vanio\DoctrineGenericTypes\Tests\Fixtures;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Vanio\DoctrineGenericTypes\DBAL\GenericType;

class BarType extends GenericType
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return '';
    }

    protected function name(): string
    {
        return 'bar';
    }
}
