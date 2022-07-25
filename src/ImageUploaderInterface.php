<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk;

use VysokeSkoly\ImageApi\Sdk\Command\DeleteImageCommand;
use VysokeSkoly\ImageApi\Sdk\Command\UploadImageCommand;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Query\GetImageQuery;
use VysokeSkoly\ImageApi\Sdk\Query\ListImagesQuery;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageHash;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageInterface;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageSize;

interface ImageUploaderInterface
{
    /**
     * @throws ImageException
     */
    public function validateAndUpload(ImageInterface $image, ImageSize $minSize): UploadImageCommand;

    /**
     * @throws ImageException
     */
    public function upload(ImageInterface $image): UploadImageCommand;

    /**
     * @throws ImageException
     */
    public function delete(ImageHash $imageHash): DeleteImageCommand;

    /**
     * @throws ImageException
     */
    public function listAllImageNames(): ListImagesQuery;

    /**
     * @throws ImageException
     */
    public function get(ImageHash $imageHash): GetImageQuery;

    /**
     * This allows the SavedImageDecoder to decode additional information about saved images.
     * Make sure you are using the decoder, when a cache is enabled, so it is cleared properly.
     *
     * @see SavedImageDecoder
     */
    public function enableCache(): void;
}
