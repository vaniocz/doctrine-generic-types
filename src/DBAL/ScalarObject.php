<?php
namespace Vanio\DoctrineGenericTypes\DBAL;

interface ScalarObject
{
    const STRING = 'string';
    const INTEGER = 'integer';
    const FLOAT = 'float';
    const BOOLEAN = 'boolean';

    static function scalarType(): string;

    /**
     * @return string|int|float|bool
     */
    function scalarValue();
}
