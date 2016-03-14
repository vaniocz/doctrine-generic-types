<?php
namespace Vanio\DoctrineGenericTypes;

class TypeGuess
{
    /** @var string */
    private $type;

    /** @var bool */
    private $nullable;

    public function __construct(string $type, bool $nullable = false)
    {
        $this->type = $type;
        $this->nullable = $nullable;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }
}
