<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use VysokeSkoly\ImageApi\Sdk\Assertion;

/**
 * @todo implement \Stringable
 */
class ImagePath implements \JsonSerializable
{
    private string $path;

    public function __construct(string $path)
    {
        Assertion::file($path);
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function __toString(): string
    {
        return $this->getPath();
    }

    public function jsonSerialize(): string
    {
        return $this->getPath();
    }

    public function getFilename(): string
    {
        $parts = explode('/', $this->path);

        return array_pop($parts);
    }
}
