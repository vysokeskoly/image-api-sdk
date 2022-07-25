<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk;

use VysokeSkoly\ImageApi\Sdk\Command\DeleteImageCommand;
use VysokeSkoly\ImageApi\Sdk\Command\UploadImageCommand;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Query\GetImageQuery;
use VysokeSkoly\ImageApi\Sdk\Query\ListImagesQuery;
use VysokeSkoly\ImageApi\Sdk\Service\CommandQueryFactory;
use VysokeSkoly\ImageApi\Sdk\Service\ImagesCache;
use VysokeSkoly\ImageApi\Sdk\Service\ImageValidator;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageHash;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageInterface;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageSize;

class ImageApiUploader implements ImageUploaderInterface
{
    private int $imageMaxSize;
    protected ImageValidator $imageValidator;
    protected CommandQueryFactory $commandQueryFactory;

    public function __construct(
        array $allowedMimeTypes,
        int $imageMaxFileSize,
        int $imageMaxSize,
        CommandQueryFactory $commandQueryFactory
    ) {
        $this->imageMaxSize = $imageMaxSize;
        $this->imageValidator = new ImageValidator($allowedMimeTypes, $imageMaxFileSize);
        $this->commandQueryFactory = $commandQueryFactory;
    }

    public function __destruct()
    {
        ImagesCache::clear();
    }

    /**
     * @throws ImageException
     */
    public function validateAndUpload(ImageInterface $image, ImageSize $minSize): UploadImageCommand
    {
        $this->imageValidator->assertValidImage($image, $minSize);
        $image = $this->resizeImage($image, $minSize);

        return $this->save($image);
    }

    private function resizeImage(ImageInterface $image, ImageSize $minSize): ImageInterface
    {
        try {
            $imageSize = $image->getSize();
            [$width, $height] = $imageSize;

            /*
             * If both dimensions are larger than max size, resize image so to have bigger dimension max size.
             * Otherwise keep original image.
             */
            if ($width > $this->imageMaxSize || $height > $this->imageMaxSize) {
                $image = $imageSize->isLandscape()
                    ? $image->scaleLandscapeTo($this->imageMaxSize)
                    : $image->scalePortraitTo($this->imageMaxSize);

                $imageSize = $image->getSize();
                [$minWidth, $minHeight] = $minSize;
                [$width, $height] = $imageSize;

                if ($width < $minWidth || $height < $minHeight) {
                    $image = $imageSize->isLandscape()
                        ? $image->scaleLandscapeTo($this->imageMaxSize)
                        : $image->scalePortraitTo($this->imageMaxSize);
                }
            }

            return $image;
        } catch (\Throwable $e) {
            throw ImageException::from($e);
        }
    }

    private function save(ImageInterface $image): UploadImageCommand
    {
        ImagesCache::storeImage($image);

        return $this->commandQueryFactory->createUploadCommand($image);
    }

    /**
     * @throws ImageException
     */
    public function upload(ImageInterface $image): UploadImageCommand
    {
        $this->imageValidator->assertImageMimeType($image);

        return $this->save($image);
    }

    /**
     * @throws ImageException
     */
    public function delete(ImageHash $imageHash): DeleteImageCommand
    {
        return $this->commandQueryFactory->createDeleteCommand($imageHash);
    }

    /**
     * @throws ImageException
     */
    public function listAllImageNames(): ListImagesQuery
    {
        return $this->commandQueryFactory->createListQuery();
    }

    /**
     * @throws ImageException
     */
    public function get(ImageHash $imageHash): GetImageQuery
    {
        return $this->commandQueryFactory->createGetQuery($imageHash);
    }

    public function enableCache(): void
    {
        ImagesCache::enable();
    }
}
