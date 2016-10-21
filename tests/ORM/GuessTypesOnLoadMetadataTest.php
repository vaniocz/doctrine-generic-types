<?php
namespace Vanio\DoctrineGenericTypes\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use PHPUnit\Framework\TestCase;
use Vanio\DoctrineGenericTypes\DBAL\UniversalJsonType;
use Vanio\DoctrineGenericTypes\ORM\GuessTypesOnLoadMetadata;
use Vanio\DoctrineGenericTypes\ORM\VarAnnotationTypeGuesser;
use Vanio\DoctrineGenericTypes\Tests\Fixtures\Entity;
use Vanio\TypeParser\TypeParser;

class GuessTypesOnLoadMetadataTest extends TestCase
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
     * @dataProvider typeGuesses
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
        $driver = $this->createMock(Driver::class);
        $driver
            ->expects($this->any())
            ->method('getDatabasePlatform')
            ->willReturn($this->createMock(AbstractPlatform::class));
        $connection = new Connection([], $driver, $config);
        $config->setMetadataDriverImpl(AnnotationDriver::create());

        return EntityManager::create($connection, $config);
    }

    public function typeGuesses(): array
    {
        return [
            ['string', Type::STRING, false],
            ['nullableString', Type::STRING, true],
            ['scalar', Type::STRING, false],
            ['object', UniversalJsonType::NAME, false],
            [\stdClass::class, UniversalJsonType::NAME, false],
            ['dateTime', Type::DATETIME, false],
            ['arrayOfStrings', Type::JSON_ARRAY, false],
            ['arrayOfScalars', Type::JSON_ARRAY, false],
            ['arrayOfObjects', UniversalJsonType::NAME, false],
            ['genericType', UniversalJsonType::NAME, false],
            ['genericTypeWithScalarParameterTypes', UniversalJsonType::NAME, false],
            ['mixed', UniversalJsonType::NAME, true],
            ['alreadyString', Type::STRING, false],
            ['stringAlreadyNullable', Type::STRING, true],
            ['notGuessable', Type::STRING, false],
        ];
    }
}
