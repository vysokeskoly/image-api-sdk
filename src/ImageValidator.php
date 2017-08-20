<?php

namespace VysokeSkoly\ImageApi\Sdk;

use Assert\Assertion;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\InvalidMimeTypeException;
use VysokeSkoly\ImageApi\Sdk\Exception\TooBigImageFileSize;
use VysokeSkoly\ImageApi\Sdk\Exception\TooSmallImage;
use VysokeSkoly\ImageApi\Sdk\Exception\UnableToLoadImageException;

class ImageValidator
{
    private const INDEX_MIN_WIDTH = 0;
    private const INDEX_MIN_HEIGHT = 1;

    /** @var array */
    private $allowedMimeTypes;

    /** @var int */
    private $maxImageSize;

    public function __construct(array $allowedMimeTypes, int $maxImageSize)
    {
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxImageSize = $maxImageSize;
    }

    public function assertValidImage(string $imagePath, int $minWidth, int $minHeight)
    {
        try {
            Assertion::file($imagePath);
            $imageInfo = getimagesize($imagePath);

            if (!is_array($imageInfo)) {
                throw UnableToLoadImageException::create();
            }

            if (!in_array($imageInfo['mime'], $this->allowedMimeTypes)) {
                throw InvalidMimeTypeException::create($imageInfo['mime'], $this->allowedMimeTypes);
            }

            if (!$this->isFileSizeCorrect($imagePath)) {
                throw TooBigImageFileSize::create($this->getHumanReadableMaxSize());
            }

            if ($imageInfo[self::INDEX_MIN_WIDTH] <= $minWidth || $imageInfo[self::INDEX_MIN_HEIGHT] <= $minHeight) {
                throw TooSmallImage::create($minHeight, $minWidth);
            }
        } catch (ImageException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ImageException::of($e);
        }
    }

    private function isFileSizeCorrect(string $image): bool
    {
        if (mb_strpos($image, '/tmp') !== false || mb_strpos($image, '/srv') !== false) {
            return (filesize($image) <= $this->maxImageSize);
        }

        if ($fp = fopen($image, 'r')) {
            $metaData = array_change_key_case(stream_get_meta_data($fp));
            $contentLength = $this->parseContentLength($metaData);
            fclose($fp);

            return $contentLength <= $this->maxImageSize;
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
        return (int) round($this->maxImageSize / (1024 * 1024), 0);
    }
}
