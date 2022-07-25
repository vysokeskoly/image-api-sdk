<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use VysokeSkoly\ImageApi\Sdk\Assertion;

class LazyImage implements ImageInterface
{
    private bool $isLoaded = false;
    private ?Image $image = null;

    public function __construct(private ImagePath $path)
    {
    }

    public function load(): void
    {
        $this->loadImage();
    }

    private function loadImage(): Image
    {
        if (!$this->isLoaded) {
            $content = ImageContent::loadFromPath($this->path);

            $this->image = new Image(
                $this->path,
                $content,
                ImageHash::createFromContent($content),
                $this->loadImagick(),
            );

            $this->isLoaded = true;
        }

        Assertion::notNull($this->image);

        return $this->image;
    }

    private function loadImagick(): \Imagick
    {
        $imagick = new \Imagick($this->path->getPath());
        $imagick->setCompressionQuality(100);

        if ($imagick->getImageFormat() === 'GIF') {
            $imagick->setImageFormat('PNG');
        }

        return $imagick;
    }

    public function getPath(): ImagePath
    {
        return $this->path;
    }

    public function getContent(): ImageContent
    {
        return $this->loadImage()->getContent();
    }

    public function getHash(): ImageHash
    {
        return $this->loadImage()->getHash();
    }

    public function getSize(): ImageSize
    {
        return $this->loadImage()->getSize();
    }

    public function getMimeType(): string
    {
        return $this->loadImage()->getMimeType();
    }

    public function getFileSize(): int
    {
        return $this->loadImage()->getFileSize();
    }

    public function asImage(): Image
    {
        return $this->loadImage();
    }

    public function scaleTo(ImageSize $size): ImageInterface
    {
        return $this->loadImage()->scaleTo($size);
    }

    public function scalePortraitTo(int $height): ImageInterface
    {
        return $this->loadImage()->scalePortraitTo($height);
    }

    public function scaleLandscapeTo(int $width): ImageInterface
    {
        return $this->loadImage()->scaleLandscapeTo($width);
    }

    public function asStream(StreamFactoryInterface $streamFactory): StreamInterface
    {
        return $this->isLoaded && $this->image
            ? $this->image->asStream($streamFactory)
            : $streamFactory->createStreamFromFile($this->path->getPath());
    }
}
