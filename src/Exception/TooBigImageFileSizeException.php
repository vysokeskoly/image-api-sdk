<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Exception;

class TooBigImageFileSizeException extends ImageException
{
    public static function create(int $maxFileSize): ImageException
    {
        return new self(sprintf('Given image is bigger than %d MB.', $maxFileSize));
    }
}
