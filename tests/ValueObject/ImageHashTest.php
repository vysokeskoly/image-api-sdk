<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use VysokeSkoly\ImageApi\Sdk\AbstractTestCase;

class ImageHashTest extends AbstractTestCase
{
    public const HASH_BRUCE = 'ec6212f1562def5ea87474b9019430214b8e3afa';

    public function testShouldLoadImageHashFromPath(): void
    {
        $hash = ImageHash::loadFromPath(new ImagePath(__DIR__ . '/../Fixtures/bruce.jpg'));

        $this->assertInstanceOf(ImageHash::class, $hash);
        $this->assertSame(self::HASH_BRUCE, $hash->getHash());
    }

    public function testShouldCreateImageHashFromContent(): void
    {
        $content = ImageContent::loadFromPath(new ImagePath(__DIR__ . '/../Fixtures/bruce.jpg'));
        $hash = ImageHash::createFromContent($content);

        $this->assertInstanceOf(ImageHash::class, $hash);
        $this->assertSame(self::HASH_BRUCE, $hash->getHash());
    }

    public function testShouldConvertImageHashToString(): void
    {
        $hash = ImageHash::loadFromPath(new ImagePath(__DIR__ . '/../Fixtures/bruce.jpg'));

        $this->assertStringable(self::HASH_BRUCE, $hash);
    }

    public function testShouldConvertImageHashToJson(): void
    {
        $hash = ImageHash::loadFromPath(new ImagePath(__DIR__ . '/../Fixtures/bruce.jpg'));

        $this->assertJsonSerializable(self::HASH_BRUCE, $hash);
    }
}
