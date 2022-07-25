<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

/** @phpstan-implements \ArrayAccess<int|string, int> */
class ImageSize implements \ArrayAccess, \JsonSerializable
{
    public static function empty(): self
    {
        return new self(0, 0);
    }

    public static function createFromImagick(\Imagick $imagick): self
    {
        return new self(
            $imagick->getImageWidth(),
            $imagick->getImageHeight(),
        );
    }

    public function __construct(private int $width, private int $height)
    {
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function isLandscape(): bool
    {
        return $this->width >= $this->height;
    }

    public function isPortrait(): bool
    {
        return $this->height >= $this->width;
    }

    public function offsetExists($offset): bool
    {
        return $offset === 0 || $offset === 1 || $offset === 'height' || $offset === 'width';
    }

    public function offsetGet($offset): ?int
    {
        return match ($offset) {
            0, 'width' => $this->getWidth(),
            1, 'height' => $this->getHeight(),
            default => null
        };
    }

    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException('Image size is immutable');
    }

    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException('Image size is immutable');
    }

    public function jsonSerialize(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
