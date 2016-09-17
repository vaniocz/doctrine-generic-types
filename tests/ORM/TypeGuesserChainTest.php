<?php
namespace Vanio\DoctrineGenericTypes\Tests;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Vanio\DoctrineGenericTypes\ORM\TypeGuess;
use Vanio\DoctrineGenericTypes\ORM\TypeGuesser;
use Vanio\DoctrineGenericTypes\ORM\TypeGuesserChain;

class TypeGuesserChainTest extends TestCase
{
    /** @var TypeGuesser */
    private $notGuessingTypeGuesser;

    /** @var TypeGuesser */
    private $stringGuessingTypeGuesser;

    /** @var TypeGuesser */
    private $integerGuessingTypeGuesser;

    /** @var ClassMetadata */
    private $metadata;

    protected function setUp()
    {
        $this->notGuessingTypeGuesser = $this->createMock(TypeGuesser::class);
        $this->notGuessingTypeGuesser->expects($this->any())->method('guessType')->willReturn(null);
        $this->stringGuessingTypeGuesser = $this->createMock(TypeGuesser::class);
        $this->stringGuessingTypeGuesser
            ->expects($this->any())
            ->method('guessType')
            ->willReturn(new TypeGuess(Type::STRING));
        $this->integerGuessingTypeGuesser = $this->createMock(TypeGuesser::class);
        $this->integerGuessingTypeGuesser
            ->expects($this->any())
            ->method('guessType')
            ->willReturn(new TypeGuess(Type::INTEGER));
        $this->metadata = new ClassMetadata('entity');
    }

    function test_the_first_guessed_type_in_the_chain_is_obtained()
    {
        $typeGuesserChain = new TypeGuesserChain($this->notGuessingTypeGuesser, $this->stringGuessingTypeGuesser);
        $this->assertSame(Type::STRING, $typeGuesserChain->guessType($this->metadata, 'property')->type());

        $typeGuesserChain = new TypeGuesserChain($this->stringGuessingTypeGuesser, $this->notGuessingTypeGuesser);
        $this->assertSame(Type::STRING, $typeGuesserChain->guessType($this->metadata, 'property')->type());

        $typeGuesserChain = new TypeGuesserChain(
            $this->notGuessingTypeGuesser,
            $this->integerGuessingTypeGuesser,
            $this->stringGuessingTypeGuesser
        );
        $this->assertSame(Type::INTEGER, $typeGuesserChain->guessType($this->metadata, 'property')->type());
    }

    function test_it_cannot_guess_when_none_of_the_guessers_in_the_chain_know()
    {
        $typeGuesserChain = new TypeGuesserChain($this->notGuessingTypeGuesser);
        $this->assertNull($typeGuesserChain->guessType($this->metadata, 'property'));
    }
}
