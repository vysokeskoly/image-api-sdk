<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Exception;

class InvalidImageException extends ImageException
{
    public static function create(): ImageException
    {
        return new self('Image is not valid.');
    }
}
