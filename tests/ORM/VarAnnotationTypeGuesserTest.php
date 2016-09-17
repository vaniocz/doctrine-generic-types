<?php
namespace Vanio\DoctrineGenericTypes\Tests;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Vanio\DoctrineGenericTypes\ORM\TypeGuess;
use Vanio\DoctrineGenericTypes\ORM\VarAnnotationTypeGuesser;
use Vanio\DoctrineGenericTypes\Tests\Fixtures\Entity;
use Vanio\TypeParser\TypeParser;

class VarAnnotationTypeGuesserTest extends TestCase
{
    /** @var VarAnnotationTypeGuesser */
    private $typeGuesser;

    /** @var ClassMetadata */
    private $metadata;

    protected function setUp()
    {
        $typeParser = new TypeParser;
        $this->typeGuesser = new VarAnnotationTypeGuesser($typeParser);
        $this->metadata = new ClassMetadata(Entity::class);
        $this->metadata->mapField(['fieldName' => 'alreadyString', 'type' => 'string']);
        $this->metadata->mapField(['fieldName' => 'stringAlreadyNullable', 'nullable' => true]);
    }

    /**
     * @dataProvider typeGuesses
     * @param string $property
     * @param string $type
     * @param bool $nullable
     */
    function test_guessing_type(string $property, string $type, bool $nullable)
    {
        $typeGuess = $this->typeGuesser->guessType($this->metadata, $property);

        $this->assertInstanceOf(TypeGuess::class, $typeGuess);
        $this->assertSame($type, $typeGuess->type());
        $this->assertSame($nullable, $typeGuess->isNullable());
    }

    function test_it_cannot_guess_type_for_not_guessable_property()
    {
        $this->assertNull($this->typeGuesser->guessType($this->metadata, 'notGuessable'));
    }

    public function typeGuesses(): array
    {
        return [
            ['string', Type::STRING, false],
            ['nullableString', Type::STRING, true],
            ['scalar', Type::STRING, false],
            ['object', Type::OBJECT, false],
            [\stdClass::class, Type::OBJECT, false],
            [\stdClass::class, Type::OBJECT, false],
            ['dateTime', Type::DATETIME, false],
            ['arrayOfStrings', Type::JSON_ARRAY, false],
            ['arrayOfScalars', Type::JSON_ARRAY, false],
            ['arrayOfObjects', Type::TARRAY, false],
            ['genericType', Type::OBJECT, false],
            ['genericTypeWithScalarParameterTypes', Type::OBJECT, false],
            ['mixed', Type::OBJECT, true],
            ['alreadyString', Type::INTEGER, false],
            ['stringAlreadyNullable', Type::STRING, false],
        ];
    }
}
