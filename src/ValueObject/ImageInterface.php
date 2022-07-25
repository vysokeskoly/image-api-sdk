<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

interface ImageInterface
{
    public function getPath(): ImagePath;

    public function getContent(): ImageContent;

    public function getHash(): ImageHash;

    public function getSize(): ImageSize;

    public function getMimeType(): string;

    public function getFileSize(): int;

    public function scaleTo(ImageSize $size): self;

    public function scalePortraitTo(int $height): self;

    public function scaleLandscapeTo(int $width): self;

    public function asStream(StreamFactoryInterface $streamFactory): StreamInterface;
}
