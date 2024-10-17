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

    public static function fromImagick(\Imagick $imagick): self
    {
        return new self($imagick->getImageBlob());
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

    /**
     * @see http://stackoverflow.com/questions/29644168/get-image-file-type-programmatically-in-swift
     * @see http://stackoverflow.com/questions/885597/string-to-byte-array-in-php
     */
    public function parseRealImageType(): ?string
    {
        $bytes = unpack('C*', mb_substr($this->content, 0, 1));

        if (!is_array($bytes)) {
            return null;
        }

        $imageTypeByte = array_shift($bytes);

        return match ($imageTypeByte) {
            0xFF, 0x3F => 'jpg',
            0x89 => 'png',
            0x47 => 'gif',
            default => null,
        };
    }
}
