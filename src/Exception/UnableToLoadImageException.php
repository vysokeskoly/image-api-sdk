<?php

namespace VysokeSkoly\ImageApi\Sdk\Exception;

class UnableToLoadImageException extends ImageException
{
    public static function create(): ImageException
    {
        return new static('Image was not able to be loaded.');
    }
}
