<?php
namespace Vanio\DoctrineGenericTypes\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Vanio\DoctrineGenericTypes\ORM\GuessTypesOnLoadMetadata;
use Vanio\DoctrineGenericTypes\ORM\VarAnnotationTypeGuesser;
use Vanio\DoctrineGenericTypes\Tests\Fixtures\Entity;
use Vanio\TypeParser\TypeParser;

class GuessTypesOnLoadMetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @var EntityManager */
    private static $entityManager;

    protected function setUp()
    {
        if (self::$entityManager) {
            return;
        }

        $typeParser = new TypeParser;
        $guessTypesOnLoadMetadata = new GuessTypesOnLoadMetadata(new VarAnnotationTypeGuesser($typeParser));
        self::$entityManager = $this->createEntityManager();
        self::$entityManager->getEventManager()->addEventSubscriber($guessTypesOnLoadMetadata);
    }

    /**
     * @dataProvider provideTypeGuesses
     * @param string $property
     * @param string $type
     * @param bool $nullable
     */
    function test_guessing_types_on_load_metadata(string $property, string $type, bool $nullable)
    {
        $metadata = self::$entityManager->getClassMetadata(Entity::class);

        $this->assertSame($type, $metadata->getTypeOfField($property));
        $this->assertSame($nullable, $metadata->isNullable($property));
    }

    private function createEntityManager(): EntityManager
    {
        $config = new Configuration;
        $config->newDefaultAnnotationDriver([], false);
        $config->setProxyDir(__DIR__);
        $config->setProxyNamespace(__NAMESPACE__ . '\Proxy');
        /** @var Driver|\PHPUnit_Framework_MockObject_MockObject $driver */
        $driver = $this->getMock(Driver::class);
        $driver
            ->expects($this->any())
            ->method('getDatabasePlatform')
            ->willReturn($this->getMock(AbstractPlatform::class));
        $connection = new Connection([], $driver, $config);
        $config->setMetadataDriverImpl(AnnotationDriver::create());

        return EntityManager::create($connection, $config);
    }

    public function provideTypeGuesses(): array
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
            ['alreadyString', Type::STRING, false],
            ['stringAlreadyNullable', Type::STRING, true],
            ['notGuessable', Type::STRING, false],
        ];
    }
}
