<?php
namespace Vanio\DoctrineGenericTypes\ORM;

class TypeGuess
{
    /** @var string */
    private $type;

    /** @var bool */
    private $nullable;

    /** @var string[] */
    private $typeParameters;

    /**
     * @param string $type
     * @param bool $nullable
     * @param string[] $typeParameters
     */
    public function __construct(string $type, bool $nullable = false, array $typeParameters = [])
    {
        $this->type = $type;
        $this->nullable = $nullable;
        $this->typeParameters = $typeParameters;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return string[]
     */
    public function typeParameters(): array
    {
        return $this->typeParameters;
    }
}
