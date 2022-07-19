<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Service;

use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageHash;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageInterface;

class ImagesCache
{
    private static bool $isEnabled = false;
    /** @phpstan-var array<string, ImageInterface> */
    private static array $cache = [];

    private function __construct()
    {
    }

    public static function enable(): void
    {
        self::$isEnabled = true;
    }

    public static function disable(): void
    {
        self::$isEnabled = false;
        self::clear();
    }

    public static function storeImage(ImageInterface $image): void
    {
        if (self::$isEnabled) {
            self::$cache[$image->getHash()->getHash()] = $image;
        }
    }

    public static function getImage(ImageHash $hash): ImageInterface
    {
        return self::$cache[$hash->getHash()];
    }

    public static function containsHash(ImageHash $hash): bool
    {
        return array_key_exists($hash->getHash(), self::$cache);
    }

    public static function removeImage(ImageHash $hash): void
    {
        if (self::containsHash($hash)) {
            unset(self::$cache[$hash->getHash()]);
        }
    }

    public static function clear(): void
    {
        self::$cache = [];
    }
}
