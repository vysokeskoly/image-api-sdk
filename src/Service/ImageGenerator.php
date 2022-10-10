<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Service;

use Imagine\Image\ImageInterface;
use Imagine\Imagick\Imagine;
use VysokeSkoly\ImageApi\Sdk\Assertion;
use VysokeSkoly\ImageApi\Sdk\ValueObject\Crop;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageContent;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageSize;

class ImageGenerator
{
    private const JPEG_QUALITY = 90;
    private const PNG_COMPRESSION_LEVEL = 9; // 0 - 9
    private const PNG_COMPRESSION_FILTER = 7; // 0 - 9

    public function generate(ImageContent $content, ImageSize $size, ?Crop $crop): ImageContent
    {
        $imageType = $content->parseRealImageType();
        Assertion::notNull($imageType, 'Could not parse an image type.');

        $imagine = new Imagine();
        $image = $imagine->load($content->getContent());

        if ($crop) {
            $image = $image->crop(
                $crop->getStart(),
                $crop->getSize()->asBox(),
            );
        }

        /** @var ImageInterface $thumbnail */
        $thumbnail = $image->thumbnail(
            $size->asBox(),
            ImageInterface::THUMBNAIL_OUTBOUND,
        );

        $thumbnailContent = $thumbnail->get(
            $imageType,
            [
                'jpeg_quality' => self::JPEG_QUALITY,
                'png_compression_level' => self::PNG_COMPRESSION_LEVEL,
                'png_compression_filter' => self::PNG_COMPRESSION_FILTER,
            ],
        );

        return new ImageContent($thumbnailContent);
    }
}
