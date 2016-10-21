<?php
namespace Vanio\DoctrineGenericTypes\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Vanio\TypeParser\Type as ParserType;

abstract class GenericType extends Type
{
    /** @var string[] */
    private $typeParameters = [];

    /**
     * @param string[] ...$typeParameters
     * @return static
     */
    final public static function create(string ...$typeParameters): self
    {
        $self = (new \ReflectionClass(static::class))->newInstanceWithoutConstructor();

        foreach ($typeParameters as $typeParameter) {
            $self->typeParameters[] = ParserType::TYPES[strtolower($typeParameter)] ?? $typeParameter;
        }

        return $self;
    }

    final public function getName(): string
    {
        return $this->typeParameters
            ? sprintf('%s<%s>', $this->name(), implode(', ', $this->typeParameters))
            : $this->name();
    }

    abstract public function name(): string;

    /**
     * @return string[]
     */
    final public function typeParameters(): array
    {
        return $this->typeParameters;
    }

    final public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
