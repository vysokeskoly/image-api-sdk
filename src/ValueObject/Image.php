<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class Image implements ImageInterface
{
    public static function loadFromPath(ImagePath $path): self
    {
        return (new LazyImage($path))->asImage();
    }

    public function __construct(
        private ImagePath $path,
        private ImageContent $content,
        private ImageHash $hash,
        private \Imagick $imagick,
    ) {
    }

    public function getPath(): ImagePath
    {
        return $this->path;
    }

    public function getContent(): ImageContent
    {
        return $this->content;
    }

    public function getHash(): ImageHash
    {
        return $this->hash;
    }

    public function getSize(): ImageSize
    {
        return ImageSize::createFromImagick($this->imagick);
    }

    public function getMimeType(): string
    {
        return $this->imagick->getImageMimeType();
    }

    public function getFileSize(): int
    {
        return $this->imagick->getImageLength();
    }

    public function scaleTo(ImageSize $size): ImageInterface
    {
        return $this->scale(fn (\Imagick $imagick) => $imagick->scaleImage($size->getWidth(), $size->getHeight(), true));
    }

    private function scale(callable $scale): ImageInterface
    {
        $imagick = clone $this->imagick;
        $scale($imagick);
        $content = new ImageContent($imagick->getImageBlob());

        return new self(
            $this->path,
            $content,
            ImageHash::createFromContent($content),
            $imagick,
        );
    }

    public function scalePortraitTo(int $height): ImageInterface
    {
        return $this->scale(fn (\Imagick $imagick) => $imagick->scaleImage(0, $height));
    }

    public function scaleLandscapeTo(int $width): ImageInterface
    {
        return $this->scale(fn (\Imagick $imagick) => $imagick->scaleImage($width, 0));
    }

    public function asStream(StreamFactoryInterface $streamFactory): StreamInterface
    {
        return $streamFactory->createStream($this->content->getContent());
    }
}
