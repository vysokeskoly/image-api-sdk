<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use VysokeSkoly\ImageApi\Sdk\AbstractTestCase;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;

class ImageContentTest extends AbstractTestCase
{
    public function testShouldLoadImageContentFromPath(): void
    {
        $path = new ImagePath(__DIR__ . '/../Fixtures/bruce.jpg');
        $content = ImageContent::loadFromPath($path);

        $this->assertInstanceOf(ImageContent::class, $content);
    }

    public function testShouldConvertImageContentToString(): void
    {
        $path = new ImagePath(__DIR__ . '/../Fixtures/bruce.jpg');
        $content = ImageContent::loadFromPath($path);

        $this->assertStringable((string) file_get_contents($path->getPath()), $content);
    }

    public function testShouldNotCreateEmptyImageContent(): void
    {
        $this->expectException(ImageException::class);

        new ImageContent('');
    }
}
