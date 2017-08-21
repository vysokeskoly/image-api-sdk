<?php

namespace VysokeSkoly\ImageApi\Sdk;

use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\InvalidImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\UnableToLoadImageContentException;

class ImageApiUploader implements ImageUploaderInterface
{
    /** @var ImageValidator */
    private $imageValidator;

    /** @var ApiUploader */
    private $apiUploader;

    /** @var string */
    private $url;

    public function __construct(array $allowedMimeTypes, int $maxImageSize, string $url)
    {
        $this->imageValidator = new ImageValidator($allowedMimeTypes, $maxImageSize);
        $this->apiUploader = new ApiUploader();
        $this->url = $url;
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
        $this->imageValidator->assertValidImage($uploadedFile, $minWidth, $minHeight);

        $imageData = $this->loadImageContent($uploadedFile);

        $image = $this->resizeImage($imageData, $minWidth, $minHeight);
        if (!$image) {
            InvalidImageException::create();
        }

        $savedImage = $this->save($image, $image->getimagewidth(), $image->getimageheight());

        return $aspectRatio !== null
            ? $savedImage->setCoordination($this->calculateCoordination($image, $aspectRatio))
            : $savedImage;
    }

    private function loadImageContent(string $uploadedFile): string
    {
        $imageData = file_get_contents($uploadedFile, false);
        if (!$imageData) {
            UnableToLoadImageContentException::create();
        }

        return $imageData;
    }

    private function resizeImage(string $imageData, int $minWidth, int $minHeight): \Gmagick
    {
        try {
            throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
        } catch (\Exception $e) {
            throw ImageException::of($e);
        }
    }

    /**
     * @param \Gmagick|string $image
     * @param int $width
     * @param int $height
     * @return Result
     */
    private function save($image, int $width, int $height): Result
    {
        $imageNameHash = sha1($image);
        $this->apiUploader->saveString($image, $imageNameHash);

        return new Result($this->url . $imageNameHash . '/', $imageNameHash, $width, $height);
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
        list($width, $height) = $this->imageValidator->assertImageMimeType($uploadedFile);

        $imageData = $this->loadImageContent($uploadedFile);

        return $this->save($imageData, $width, $height);
    }
}
