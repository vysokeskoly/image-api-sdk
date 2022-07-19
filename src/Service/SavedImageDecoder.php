<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Service;

use Lmc\Cqrs\Types\Decoder\ImpureResponseDecoderInterface;
use Lmc\Cqrs\Types\ValueObject\DecodedValue;
use Lmc\Cqrs\Types\ValueObject\DecodedValueInterface;
use VysokeSkoly\ImageApi\Sdk\Command\UploadImageCommand;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageHash;
use VysokeSkoly\ImageApi\Sdk\ValueObject\SavedImage;

/**
 * @phpstan-implements ImpureResponseDecoderInterface<array, DecodedValueInterface<array<SavedImage>>>
 */
class SavedImageDecoder implements ImpureResponseDecoderInterface
{
    private string $imageBaseUrl;

    public function __construct(string $imageBaseUrl)
    {
        $this->imageBaseUrl = $imageBaseUrl;
    }

    public function supports($response, $initiator): bool
    {
        return $initiator instanceof UploadImageCommand
            && is_array($response)
            && array_key_exists('isSuccess', $response)
            && $response['isSuccess'] === true
            && array_key_exists('messages', $response);
    }

    /** @phpstan-return DecodedValueInterface<array<SavedImage>> */
    public function decode($response): DecodedValueInterface
    {
        $decoded = array_map(
            function ($hash) {
                $imageHash = new ImageHash($hash);

                if (ImagesCache::containsHash($imageHash)) {
                    $image = ImagesCache::getImage($imageHash);
                    ImagesCache::removeImage($imageHash);

                    return SavedImage::createFromImage($this->imageBaseUrl, $image);
                }

                return SavedImage::createFromHash($this->imageBaseUrl, $imageHash);
            },
            $response['messages'] ?? []
        );

        return new DecodedValue($decoded);
    }
}
