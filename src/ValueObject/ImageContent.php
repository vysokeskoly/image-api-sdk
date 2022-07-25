<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use VysokeSkoly\ImageApi\Sdk\Assertion;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;

class ImageContent implements \Stringable
{
    public static function loadFromPath(ImagePath $path): self
    {
        try {
            $content = file_get_contents($path->getPath());
            Assertion::string($content);

            return new self($content);
        } catch (\Throwable $e) {
            throw ImageException::from($e);
        }
    }

    public function __construct(private string $content)
    {
        Assertion::notEmpty($content);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function __toString(): string
    {
        return $this->getContent();
    }
}
