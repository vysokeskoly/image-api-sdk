<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Exception;

use Assert\AssertionFailedException;

class ImageException extends \InvalidArgumentException implements ImageExceptionInterface, AssertionFailedException
{
    private ?string $propertyPath = null;
    /** @var mixed */
    private $value = null;
    private array $constraints = [];

    public static function from(\Throwable $e): self
    {
        // todo - use previous: $e on php 8.1
        return new self($e->getMessage(), null, null, null, [], $e);
    }

    /**
     * @param mixed $value
     * @phpstan-param mixed[] $constraints
     */
    public function __construct(
        string $message,
        int $code = null,
        ?string $propertyPath = null,
        $value = null,
        array $constraints = [],
        \Throwable $previous = null
    ) {
        parent::__construct($message, (int) $code, $previous);
        $this->propertyPath = $propertyPath;
        $this->value = $value;
        $this->constraints = $constraints;
    }

    public function getPropertyPath(): ?string
    {
        return $this->propertyPath;
    }

    /** @return mixed */
    public function getValue()
    {
        return $this->value;
    }

    /** @phpstan-return mixed[] */
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
