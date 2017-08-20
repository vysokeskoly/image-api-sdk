<?php

namespace VysokeSkoly\ImageApi\Sdk\Exception;

class TooSmallImage extends ImageException
{
    public static function create(int $minHeight, int $minWidth): ImageException
    {
        return new static(
            sprintf(
                'Given image is too small. Image should be bigger than %d x %d px.',
                $minWidth,
                $minHeight
            )
        );
    }
}
