<?php

namespace VysokeSkoly\ImageApi\Sdk;

use GuzzleHttp\Client;
use VysokeSkoly\ImageApi\Sdk\Entity\Coordination;
use VysokeSkoly\ImageApi\Sdk\Entity\Result;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Service\ApiService;
use VysokeSkoly\ImageApi\Sdk\Service\ImageFactory;
use VysokeSkoly\ImageApi\Sdk\Service\ImageValidator;

class ImageApiUploader implements ImageUploaderInterface
{
    /** @var int */
    private $imageMaxSize;

    /** @var string */
    private $imageUrl;

    /** @var ImageValidator */
    protected $imageValidator;

    /** @var ApiService */
    protected $apiService;

    /** @var ImageFactory */
    protected $imageFactory;

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
        $this->apiService = new ApiService(new Client(), $apiUrl, $apiKey);
        $this->imageFactory = new ImageFactory();
        $this->imageUrl = $imageUrl;
    }

    /**
     * @param string $imagePath Full path file name
     * @param int $minWidth
     * @param int $minHeight
     * @param float|null $aspectRatio
     * @return Result
     *
     * @throws ImageException
     */
    public function validateAndUpload(
        string $imagePath,
        int $minWidth,
        int $minHeight,
        float $aspectRatio = null
    ): Result {
        $this->imageValidator->assertValidImage($imagePath, $minWidth, $minHeight);

        $imageData = $this->loadImageContent($imagePath);
        $image = $this->resizeImage($imageData, $minWidth, $minHeight);

        $savedImage = $this->save($image, $image->getimagewidth(), $image->getimageheight());

        return $aspectRatio !== null
            ? $savedImage->setCoordination($this->calculateCoordination($image, $aspectRatio))
            : $savedImage;
    }

    private function loadImageContent(string $uploadedFile): string
    {
        return file_get_contents($uploadedFile, false);
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
        $this->apiService->saveString((string) $image, $imageNameHash);

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
     * @param string $imagePath Full path file name
     * @return Result
     *
     * @throws ImageException
     */
    public function upload(string $imagePath): Result
    {
        list($width, $height) = $this->imageValidator->assertImageMimeType($imagePath);

        $imageData = $this->loadImageContent($imagePath);

        return $this->save($imageData, $width, $height);
    }

    /**
     * @param string $imageName
     *
     * @throws ImageException
     */
    public function delete(string $imageName): void
    {
        $this->apiService->delete($imageName);
    }

    /**
     * @return array
     *
     * @throws ImageException
     */
    public function listAllImageNames(): array
    {
        return $this->apiService->listAll();
    }

    /**
     * @param string $fileName
     * @return string
     *
     * @throws ImageException
     */
    public function get(string $fileName): string
    {
        return $this->apiService->get($fileName);
    }
}
