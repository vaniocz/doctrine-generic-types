<?php
namespace Vanio\DoctrineGenericTypes\DBAL;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use Vanio\Stdlib\Objects;

/**
 * A registry of types replacing the Doctrine internal flyweight system.
 * @internal
 */
class GenericTypeRegistry implements \ArrayAccess
{
    /** @var array */
    private $types;

    /** @var bool */
    private $removing = false;

    public function __construct()
    {
    }

    /**
     * Register itself, hook into Doctrine flyweight system.
     */
    public function register()
    {
        if ($this->types !== null) {
            return;
        }

        $types = &Objects::getPropertyValue(Type::class, '_typeObjects', Type::class);

        if ($types instanceof self) {
            $this->types = $types->types;
        } else {
            $this->types = [];

            foreach ($types as $name => $type) {
                $this->addType($type);
            }
        }

        $types = $this;
    }

    /**
     * Get an instance of the given type name
     *
     * @param string $type
     * @return Type
     * @throws DBALException
     */
    public function getType(string $type)
    {
        if (isset($this->types[$type])) {
            return $this->types[$type];
        }

        $instance = $this->createType($type);
        $this->addType($instance);
        $this->types[$type] = $instance;

        return $instance;
    }

    /**
     * Register the given type instance
     *
     * @param Type $type
     * @throws DBALException
     */
    public function addType(Type $type)
    {
        $name = $type->getName();

        if (!Type::hasType($name)) {
            Type::addType($name, get_class($type));
        }

        $this->types[$name] = $type;
    }

    /**
     * Unregister the given type
     *
     * @param string $type
     */
    public function removeType(string $type)
    {
        if (!$this->removing && Type::hasType($type)) {
            $this->removing = true;
            Type::overrideType($type, null);
        }

        $this->removing = false;
        unset($this->types[$type]);
    }

    /**
     * Return true so doctrine always delegates creation of the type.
     *
     * @param string $type
     * @return bool
     */
    public function offsetExists($type): bool
    {
        return true;
    }

    /**
     * Get an instance of the given type name
     *
     * @param string $type
     * @return Type
     * @throws DBALException
     */
    public function offsetGet($type): Type
    {
        return $this->getType($type);
    }

    /**
     * Register the given type instance
     *
     * @param string $name not used
     * @param Type $type
     * @throws DBALException
     */
    public function offsetSet($name, $type)
    {
        $this->addType($type);
    }

    /**
     * Unregister the given type
     *
     * @param string $type
     */
    public function offsetUnset($type)
    {
        $this->removeType($type);
    }

    /**
     * @param string $type
     * @return Type
     * @throws DBALException
     */
    private function createType(string $type): Type
    {
        $typeParameters = [];

        if (!$class = Type::getTypesMap()[$type] ?? null) {
            list($type, $typeParameters) = $this->resolveTypeParameters($type);

            if (!$class = Type::getTypesMap()[$type] ?? null) {
                throw DBALException::unknownColumnType($type);
            }
        }

        return is_a($class, GenericType::class, true)
            ? $class::{'create'}(...$typeParameters)
            : (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    }

    /**
     * @param string $type
     * @return array
     */
    private function resolveTypeParameters(string $type): array
    {
        list($type, $typeParametersLiteral) = explode('<', $type, 2) + [1 => null];
        $typeParameters = $typeParametersLiteral
            ? preg_split('~,\h*~', trim(substr($typeParametersLiteral, 0, -1)))
            : [];

        return [$type, $typeParameters];
    }
}
