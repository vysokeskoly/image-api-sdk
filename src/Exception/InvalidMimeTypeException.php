<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Exception;

class InvalidMimeTypeException extends ImageException
{
    public static function create(string $givenMimeType, array $availableMimeTypes): ImageException
    {
        return new self(
            sprintf(
                'Image of type "%s" given and it was expected one of ["%s"].',
                $givenMimeType,
                implode('", "', $availableMimeTypes)
            )
        );
    }
}
