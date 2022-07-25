<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

class SavedImage implements \JsonSerializable
{
    private string $baseUrl;
    private ImageHash $hash;
    private ImageSize $size;

    public static function createFromImage(string $baseUrl, ImageInterface $image): self
    {
        return new self($baseUrl, $image->getHash(), $image->getSize());
    }

    public static function createFromHash(string $baseUrl, ImageHash $hash): self
    {
        return new self($baseUrl, $hash, ImageSize::empty());
    }

    public function __construct(string $baseUrl, ImageHash $hash, ImageSize $size)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->hash = $hash;
        $this->size = $size;
    }

    public function getUrl(): string
    {
        return sprintf('%s/%s/', $this->baseUrl, $this->hash);
    }

    public function getHash(): ImageHash
    {
        return $this->hash;
    }

    public function getSize(): ImageSize
    {
        return $this->size;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->getUrl(),
            'hash' => $this->getHash()->getHash(),
            'width' => $this->getSize()->getWidth(),
            'height' => $this->getSize()->getHeight(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
