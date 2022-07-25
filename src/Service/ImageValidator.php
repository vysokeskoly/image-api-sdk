<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Service;

use VysokeSkoly\ImageApi\Sdk\Exception\InvalidMimeTypeException;
use VysokeSkoly\ImageApi\Sdk\Exception\TooBigImageFileSizeException;
use VysokeSkoly\ImageApi\Sdk\Exception\TooSmallImageException;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageInterface;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageSize;

/** @internal */
class ImageValidator
{
    private array $allowedMimeTypes;
    private int $maxImageFileSize;

    public function __construct(array $allowedMimeTypes, int $maxImageFileSize)
    {
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxImageFileSize = $maxImageFileSize;
    }

    public function assertValidImage(ImageInterface $image, ImageSize $minSize): void
    {
        $this->assertImageMimeType($image);
        $this->assertFileSize($image);
        $this->assertImageSize($image, $minSize);
    }

    public function assertImageMimeType(ImageInterface $image): void
    {
        $mimeType = $image->getMimeType();

        if (!in_array($mimeType, $this->allowedMimeTypes, true)) {
            throw InvalidMimeTypeException::create($mimeType, $this->allowedMimeTypes);
        }
    }

    private function assertFileSize(ImageInterface $image): void
    {
        if ($image->getFileSize() > $this->maxImageFileSize) {
            throw TooBigImageFileSizeException::create($this->getHumanReadableMaxSize());
        }
    }

    private function getHumanReadableMaxSize(): int
    {
        return (int) round($this->maxImageFileSize / (1024 * 1024), 0);
    }

    private function assertImageSize(ImageInterface $image, ImageSize $minSize): void
    {
        $imageSize = $image->getSize();

        if ($imageSize->getWidth() < $minSize->getWidth() || $imageSize->getHeight() < $minSize->getHeight()) {
            throw TooSmallImageException::create($minSize);
        }
    }
}
