<?php
namespace Vanio\DoctrineGenericTypes\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Entity
{
    /**
     * @var string
     * @ORM\Column
     * @ORM\Id
     */
    public $string;

    /**
     * @var string|null
     * @ORM\Column
     */
    public $nullableString;

    /**
     * @var int|string
     * @ORM\Column
     */
    public $scalar;

    /**
     * @var object
     * @ORM\Column
     */
    public $object;

    /**
     * @var \stdClass
     * @ORM\Column
     */
    public $stdClass;

    /**
     * @var \DateTime
     * @ORM\Column
     */
    public $dateTime;

    /**
     * @var array
     * @ORM\Column
     */
    public $array;

    /**
     * @var string[]
     * @ORM\Column
     */
    public $arrayOfStrings;

    /**
     * @var scalar[]
     * @ORM\Column
     */
    public $arrayOfScalars;

    /**
     * @var object[]
     * @ORM\Column
     */
    public $arrayOfObjects;

    /**
     * @var \ArrayIterator<\stdClass>
     * @ORM\Column
     */
    public $genericType;

    /**
     * @var \ArrayIterator<int, string>
     * @ORM\Column
     */
    public $genericTypeWithScalarParameterTypes;

    /**
     * @var mixed
     * @ORM\Column
     */
    public $mixed;

    /**
     * @var int
     * @ORM\Column(type="string")
     */
    public $alreadyString;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    public $stringAlreadyNullable;

    /**
     * @ORM\Column
     */
    public $notGuessable;
}
