<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

/** @phpstan-implements \ArrayAccess<int|string, int> */
class ImageSize implements \ArrayAccess, \JsonSerializable
{
    private int $width;
    private int $height;

    public static function empty(): self
    {
        return new self(0, 0);
    }

    public static function createFromImagick(\Imagick $imagick): self
    {
        return new self(
            $imagick->getImageWidth(),
            $imagick->getImageHeight()
        );
    }

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
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
        if ($offset === 0 || $offset === 'width') {
            return $this->getWidth();
        }
        if ($offset === 1 || $offset === 'height') {
            return $this->getHeight();
        }

        return null;
        // todo - use match on php 8.1
        // switch ($offset) {
        //     case 'width':
        //     case 0:
        //         return $this->getWidth();
        //     case 'height':
        //     case 1:
        //         return $this->getHeight();
        //     default:
        //         return null;
        // }
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
