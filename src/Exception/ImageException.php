<?php

namespace VysokeSkoly\ImageApi\Sdk\Exception;

class ImageException extends \InvalidArgumentException
{
    public static function of(\Throwable $e): ImageException
    {
        return new static($e->getMessage(), $e);
    }

    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
