<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Service;

use VysokeSkoly\ImageApi\Sdk\Assertion;
use VysokeSkoly\ImageApi\Sdk\ValueObject\Crop;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageContent;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageSize;

class ImageGenerator
{
    public function generate(ImageContent $content, ImageSize $size, ?Crop $crop): ImageContent
    {
        $imagick = $this->createImagickFromContent($content);

        if ($crop) {
            $cropStart = $crop->getStart();
            $cropSize = $crop->getSize();

            Assertion::true(
                $imagick->cropImage(
                    $cropSize->getWidth(),
                    $cropSize->getHeight(),
                    $cropStart->getX(),
                    $cropStart->getY(),
                ),
            );
        }

        Assertion::true($imagick->thumbnailImage($size->getWidth(), $size->getHeight()));

        return ImageContent::fromImagick($imagick);
    }

    private function createImagickFromContent(ImageContent $content): \Imagick
    {
        $imagick = new \Imagick();
        $imagick->readImageBlob($content->getContent());

        return $imagick;
    }
}
