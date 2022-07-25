<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Exception;

use Assert\AssertionFailedException;

class ImageException extends \InvalidArgumentException implements ImageExceptionInterface, AssertionFailedException
{
    public static function from(\Throwable $e): self
    {
        return new self($e->getMessage(), previous: $e);
    }

    /**
     * @phpstan-param mixed[] $constraints
     */
    public function __construct(
        string $message,
        int $code = null,
        private ?string $propertyPath = null,
        private mixed $value = null,
        private array $constraints = [],
        \Throwable $previous = null,
    ) {
        parent::__construct($message, (int) $code, $previous);
    }

    public function getPropertyPath(): ?string
    {
        return $this->propertyPath;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    /** @phpstan-return mixed[] */
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
