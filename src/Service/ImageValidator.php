<?php

namespace VysokeSkoly\ImageApi\Sdk\Service;

use Assert\Assertion;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\InvalidMimeTypeException;
use VysokeSkoly\ImageApi\Sdk\Exception\TooBigImageFileSizeException;
use VysokeSkoly\ImageApi\Sdk\Exception\TooSmallImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\UnableToLoadImageException;

class ImageValidator
{
    private const INDEX_MIN_WIDTH = 0;
    private const INDEX_MIN_HEIGHT = 1;

    /** @var array */
    private $allowedMimeTypes;

    /** @var int */
    private $maxImageFileSize;

    public function __construct(array $allowedMimeTypes, int $maxImageFileSize)
    {
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxImageFileSize = $maxImageFileSize;
    }

    public function assertValidImage(string $imagePath, int $minWidth, int $minHeight): void
    {
        try {
            $imageInfo = $this->parseImageInfo($imagePath);
            $this->assertMimeType($imageInfo);

            if (!$this->isFileSizeCorrect($imagePath)) {
                throw TooBigImageFileSizeException::create($this->getHumanReadableMaxSize());
            }

            if ($imageInfo[self::INDEX_MIN_WIDTH] <= $minWidth || $imageInfo[self::INDEX_MIN_HEIGHT] <= $minHeight) {
                throw TooSmallImageException::create($minHeight, $minWidth);
            }
        } catch (ImageException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ImageException::from($e);
        }
    }

    private function parseImageInfo(string $imagePath): array
    {
        Assertion::file($imagePath);
        $imageInfo = getimagesize($imagePath);

        if (!is_array($imageInfo)) {
            throw UnableToLoadImageException::create();
        }

        return $imageInfo;
    }

    private function assertMimeType(array $imageInfo): void
    {
        if (!in_array($imageInfo['mime'], $this->allowedMimeTypes)) {
            throw InvalidMimeTypeException::create($imageInfo['mime'], $this->allowedMimeTypes);
        }
    }

    private function isFileSizeCorrect(string $image): bool
    {
        if (mb_strpos($image, '/tmp') !== false || mb_strpos($image, '/srv') !== false) {
            return (filesize($image) <= $this->maxImageFileSize);
        }

        if ($fp = fopen($image, 'r')) {
            $metaData = array_change_key_case(stream_get_meta_data($fp));
            $contentLength = $this->parseContentLength($metaData);
            fclose($fp);

            return $contentLength <= $this->maxImageFileSize;
        }

        return false;
    }

    /**
     * $metaData[wrapper_data][6] => 'Content-Length: 1234'
     */
    private function parseContentLength(array $metaData): int
    {
        return (int) array_pop(explode(' ', $metaData['wrapper_data'][6]));
    }

    private function getHumanReadableMaxSize(): int
    {
        return (int) round($this->maxImageFileSize / (1024 * 1024), 0);
    }

    public function assertImageMimeType(string $uploadedFile): array
    {
        $imageInfo = $this->parseImageInfo($uploadedFile);
        $this->assertMimeType($imageInfo);

        return $imageInfo;
    }
}
