<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Exception;

use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageSize;

class TooSmallImageException extends ImageException
{
    public static function create(ImageSize $minSize): ImageException
    {
        return new self(
            sprintf(
                'Given image is too small. Image should be bigger than %d x %d px.',
                $minSize->getWidth(),
                $minSize->getHeight()
            )
        );
    }
}
