<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

class ImageHash implements \JsonSerializable, \Stringable
{
    public static function createFromContent(ImageContent $content): self
    {
        return new self(sha1($content->getContent()));
    }

    public static function loadFromPath(ImagePath $path): self
    {
        return self::createFromContent(ImageContent::loadFromPath($path));
    }

    public function __construct(private string $hash)
    {
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function __toString(): string
    {
        return $this->getHash();
    }

    public function jsonSerialize(): string
    {
        return $this->getHash();
    }
}
