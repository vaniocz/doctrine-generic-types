<?php
namespace Vanio\DoctrineGenericTypes\Tests\Fixtures;

class Entity
{
    /** @var string */
    public $string;

    /** @var string|null */
    public $nullableString;

    /** @var int|string */
    public $scalar;

    /** @var object */
    public $object;

    /** @var \stdClass */
    public $stdClass;

    /** @var \DateTime */
    public $dateTime;

    /** @var array */
    public $array;

    /** @var string[] */
    public $arrayOfStrings;

    /** @var scalar[] */
    public $arrayOfScalars;

    /** @var object[] */
    public $arrayOfObjects;

    /** @var \ArrayIterator<\stdClass> */
    public $genericType;

    /** @var \ArrayIterator<int, string> */
    public $genericTypeWithScalarParameterTypes;

    /** @var mixed */
    public $mixed;

    public $notGuessable;
}
