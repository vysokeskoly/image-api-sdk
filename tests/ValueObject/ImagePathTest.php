<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use VysokeSkoly\ImageApi\Sdk\AbstractTestCase;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;

class ImagePathTest extends AbstractTestCase
{
    public function testShouldCreateImagePath(): void
    {
        $path = new ImagePath(__DIR__ . '/../Fixtures/bruce.jpg');

        $this->assertInstanceOf(ImagePath::class, $path);
    }

    public function testShouldConvertImagePathToString(): void
    {
        $path = __DIR__ . '/../Fixtures/bruce.jpg';
        $imagePath = new ImagePath($path);

        $this->assertStringable($path, $imagePath);
    }

    public function testShouldConvertImagePathToJson(): void
    {
        $path = __DIR__ . '/../Fixtures/bruce.jpg';
        $imagePath = new ImagePath($path);

        $this->assertJsonSerializable($path, $imagePath);
    }

    public function testShouldNotCreateImagePathFromInvalidPath(): void
    {
        $this->expectException(ImageException::class);

        new ImagePath(__DIR__ . '/../Fixtures/invalid.jpg');
    }
}
