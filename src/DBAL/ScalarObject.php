<?php
namespace Vanio\DoctrineGenericTypes\DBAL;

interface ScalarObject
{
    const STRING = 'string';
    const INTEGER = 'int';
    const FLOAT = 'float';
    const BOOLEAN = 'bool';

    static function scalarType(): string;

    /**
     * @return string|int|float|bool
     */
    function scalarValue();
}
