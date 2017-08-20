<?php

namespace VysokeSkoly\ImageApi\Sdk\Exception;

class UnableToLoadImageContentException extends ImageException
{
    public static function create(): ImageException
    {
        return new static('There was a problem with loading image content.');
    }
}
