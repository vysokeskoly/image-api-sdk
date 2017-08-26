<?php

namespace VysokeSkoly\ImageApi\Sdk;

use GuzzleHttp\Client;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\InvalidImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\UnableToLoadImageContentException;

class ImageApiUploader implements ImageUploaderInterface
{
    /** @var int */
    private $imageMaxSize;

    /** @var string */
    private $imageUrl;

    /** @var ImageValidator */
    private $imageValidator;

    /** @var ApiUploader */
    private $apiUploader;

    /** @var ImageFactory */
    private $imageFactory;

    public function __construct(
        array $allowedMimeTypes,
        int $imageMaxFileSize,
        int $imageMaxSize,
        string $imageUrl,
        string $apiUrl,
        string $apiKey
    ) {
        $this->imageMaxSize = $imageMaxSize;
        $this->imageValidator = new ImageValidator($allowedMimeTypes, $imageMaxFileSize);
        $this->apiUploader = new ApiUploader(new Client(), $apiUrl, $apiKey);
        $this->imageFactory = new ImageFactory();
        $this->imageUrl = $imageUrl;
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
            $image = $this->imageFactory->createImage($imageData);

            $width = $image->getimagewidth();
            $height = $image->getimageheight();

            /*
             * If both dimensions are larger than max size, resize image so to have bigger dimension max size.
             * Otherwise keep original image.
             */
            if ($width > $this->imageMaxSize || $height > $this->imageMaxSize) {
                if ($width >= $height) {
                    $image->scaleimage($this->imageMaxSize, 0);
                } else {
                    $image->scaleimage(0, $this->imageMaxSize);
                }

                $width = $image->getimagewidth();
                $height = $image->getimageheight();

                if ($width < $minWidth || $height < $minHeight) {
                    if ($width >= $height) {
                        $image->scaleimage(0, $minHeight);
                    } else {
                        $image->scaleimage($minWidth, 0);
                    }
                }
            }

            return $image;
        } catch (\Exception $e) {
            throw ImageException::from($e);
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

        return new Result($this->imageUrl . $imageNameHash . '/', $imageNameHash, $width, $height);
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
