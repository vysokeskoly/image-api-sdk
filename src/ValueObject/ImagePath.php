<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use VysokeSkoly\ImageApi\Sdk\Assertion;

class ImagePath implements \JsonSerializable, \Stringable
{
    public function __construct(private string $path)
    {
        Assertion::file($path);
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
