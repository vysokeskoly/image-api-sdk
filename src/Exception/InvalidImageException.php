<?php

namespace VysokeSkoly\ImageApi\Sdk\Exception;

class InvalidImageException extends ImageException
{
    public static function create(): ImageException
    {
        return new static('Image is not valid.');
    }
}
