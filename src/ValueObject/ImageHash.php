<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

/**
 * @todo implement \Stringable
 */
class ImageHash implements \JsonSerializable
{
    private string $hash;

    public static function createFromContent(ImageContent $content): self
    {
        return new self(sha1($content->getContent()));
    }

    public static function loadFromPath(ImagePath $path): self
    {
        return self::createFromContent(ImageContent::loadFromPath($path));
    }

    public function __construct(string $hash)
    {
        $this->hash = $hash;
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
