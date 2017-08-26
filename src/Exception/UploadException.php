<?php

namespace VysokeSkoly\ImageApi\Sdk\Exception;

class UploadException extends ImageException
{
    public static function create(int $statusCode, string $contents): ImageException
    {
        return new static(sprintf('Upload ended up with exception (%d): "%s"', $statusCode, $contents));
    }
}
