<?php

namespace VysokeSkoly\ImageApi\Sdk\Exception;

class TooBigImageFileSizeException extends ImageException
{
    public static function create(int $maxFileSize): ImageException
    {
        return new static(sprintf('Given image is bigger than %d MB.', $maxFileSize));
    }
}
