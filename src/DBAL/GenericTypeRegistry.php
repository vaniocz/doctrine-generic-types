<?php
namespace Vanio\DoctrineGenericTypes\DBAL;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\TypeRegistry;
use Vanio\Stdlib\Objects;

/**
 * A registry of types decorating the Doctrine internal TypeRegistry.
 * @internal
 */
class GenericTypeRegistry
{
    /** @var TypeRegistry|null */
    private $typeRegistry;

    /** @var string[] */
    private $genericTypes = [];

    /** @var bool */
    private $removing = false;

    /**
     * Register itself, hook into Doctrine flyweight system.
     */
    public function hook()
    {
        if ($this->typeRegistry !== null) {
            return;
        }

        $typeRegistry = Type::getTypeRegistry();

        if (!$typeRegistry instanceof self) {
            $this->genericTypes = [];
            $this->typeRegistry = $typeRegistry;

            Objects::setPropertyValue(Type::class, 'typeRegistry', $this, Type::class);
        }
    }

    /**
     * Finds a type by the given name.
     *
     * @throws Exception
     */
    public function get(string $type): Type
    {
        try {
            $instance = $this->typeRegistry->get($type);
        } catch (Exception $e) {
            $instance = $this->createType($type);
            $this->typeRegistry->register($type, $instance);
        }

        return $instance;
    }

    /**
     * Finds a name for the given type.
     *
     * @throws Exception
     */
    public function lookupName(Type $type): string
    {
        return $this->typeRegistry->lookupName($type);
    }

    /**
     * Unregister the given type
     */
    public function removeType(string $type)
    {
        if (!$this->removing && Type::hasType($type)) {
            $this->removing = true;
            Type::overrideType($type, null);
        }

        $this->removing = false;
    }

    /**
     * Checks if there is a type of the given name.
     */
    public function has(string $name): bool
    {
        return $this->typeRegistry->has($name);
    }

    /**
     * Registers a custom type to the type map.
     *
     * @throws Exception
     */
    public function register(string $name, Type $type): void
    {
        if (is_a($type, GenericType::class, true)) {
            $this->genericTypes[$name] = $type::class;
        } else {
            $this->typeRegistry->register($name, $type);
        }
    }

    /**
     * Overrides an already defined type to use a different implementation.
     *
     * @throws Exception
     */
    public function override(string $name, Type $type)
    {
        if (is_a($type, GenericType::class, true)) {
            $this->genericTypes[$name] = $type::class;
        } else {
            $this->typeRegistry->override($name, $type);
        }
    }

    /**
     * Gets the map of all registered types and their corresponding type instances.
     *
     * @internal
     *
     * @return array<string, Type>
     */
    public function getMap(): array
    {
        return $this->typeRegistry->getMap();
    }

    /**
     * @throws Exception
     */
    private function createType(string $type): Type
    {
        list($type, $typeParameters) = $this->resolveTypeParameters($type);

        if ($class = $this->genericTypes[$type] ?? null) {
            return $class::{'create'}(...$typeParameters);
        } elseif ($class = Type::getTypesMap()[$type] ?? null) {
            return (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        }

        throw Exception::unknownColumnType($type);
    }

    private function resolveTypeParameters(string $type): array
    {
        list($type, $typeParametersLiteral) = explode('<', $type, 2) + [1 => null];
        $typeParameters = $typeParametersLiteral
            ? preg_split('~,\h*~', trim(substr($typeParametersLiteral, 0, -1)))
            : [];

        return [$type, $typeParameters];
    }
}
