<?php

namespace VysokeSkoly\ImageApi\Sdk;

use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\InvalidImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\UnableToLoadImageContentException;

class ImageApiUploader implements ImageUploaderInterface
{
    /** @var array */
    private $allowedMimeTypes;

    /** @var int */
    private $maxImageSize;

    public function __construct(array $allowedMimeTypes, int $maxImageSize)
    {
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxImageSize = $maxImageSize;
    }

    /**
     * @param string $uploadedFile Full path file name
     * @param int $minWidth
     * @param int $minHeight
     * @param float|null $aspectRatio
     * @return Result
     *
     * @throws ImageException
     */
    public function validateAndUpload(
        string $uploadedFile,
        int $minWidth,
        int $minHeight,
        float $aspectRatio = null
    ): Result {
        $imageValidator = new ImageValidator($this->allowedMimeTypes, $this->maxImageSize);
        $imageValidator->assertValidImage($uploadedFile, $minWidth, $minHeight);

        $imageData = file_get_contents($uploadedFile, false);
        if (!$imageData) {
            UnableToLoadImageContentException::create();
        }

        $image = $this->resizeImage($imageData, $minWidth, $minHeight);
        if (!$image) {
            InvalidImageException::create();
        }

        $savedImage = $this->save($image);

        return $aspectRatio !== null
            ? $savedImage->setCoordination($this->calculateCoordination($image, $aspectRatio))
            : $savedImage;
    }

    private function resizeImage(string $imageData, int $minWidth, int $minHeight): \Gmagick
    {
        try {
            throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
        } catch (\Exception $e) {
            throw ImageException::of($e);
        }
    }

    private function save(\Gmagick $image): Result
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    private function calculateCoordination(\Gmagick $image, float $aspectRatio): Coordination
    {
        $width = $image->getimagewidth();
        $height = $image->getimageheight();

        if ($aspectRatio > ($width / $height)) {
            $heightCounted = $width / $aspectRatio;
            $padding = abs(($height - $heightCounted) / 2);
            $coordination = new Coordination(0, $padding, $width, $heightCounted + $padding);
        } else {
            $widthCounted = $height * $aspectRatio;
            $padding = abs(($width - $widthCounted) / 2);
            $coordination = new Coordination($padding, 0, $widthCounted + $padding, $height);
        }

        return $coordination;
    }

    /**
     * @param string $uploadedFile Full path file name
     * @return Result
     *
     * @throws ImageException
     */
    public function upload(string $uploadedFile): Result
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }
}
