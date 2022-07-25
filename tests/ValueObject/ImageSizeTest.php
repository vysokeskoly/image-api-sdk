<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use VysokeSkoly\ImageApi\Sdk\AbstractTestCase;

class ImageSizeTest extends AbstractTestCase
{
    public function testShouldCreateImageSizeFromImagick(): void
    {
        $imagick = new \Imagick(__DIR__ . '/../Fixtures/bruce.jpg');
        $size = ImageSize::createFromImagick($imagick);

        $this->assertEquals(new ImageSize(100, 132), $size);
    }

    public function testShouldDestructureSizeAsArray(): void
    {
        $size = new ImageSize(200, 300);

        $this->assertTrue(isset($size[0]));
        $this->assertTrue(isset($size[1]));
        $this->assertTrue(isset($size['width']));
        $this->assertTrue(isset($size['height']));

        $this->assertSame(200, $size[0]);
        $this->assertSame(300, $size[1]);

        $this->assertSame(200, $size['width']);
        $this->assertSame(300, $size['height']);

        [$width, $height] = $size;
        $this->assertSame(200, $width);
        $this->assertSame(300, $height);

        ['height' => $height, 'width' => $width] = $size;
        $this->assertSame(200, $width);
        $this->assertSame(300, $height);
    }

    public function testShouldReturnNullOnUnknownOffset(): void
    {
        $size = new ImageSize(200, 300);

        $this->assertFalse(isset($size[42]));
        $this->assertFalse(isset($size['unknown']));

        $this->assertNull($size[42]);
        $this->assertNull($size['unknown']);
    }

    public function testShouldNotAllowToChangeSize(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $size = new ImageSize(200, 300);
        $size['height'] = 100;
    }

    public function testShouldNotAllowToClearSize(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $size = new ImageSize(200, 300);
        unset($size[0]);
    }

    /** @dataProvider provideOrientation */
    public function testShouldDetermineOrientation(int $width, int $height, bool $isLandscape, bool $isPortrait): void
    {
        $size = new ImageSize($width, $height);

        $this->assertSame($isLandscape, $size->isLandscape());
        $this->assertSame($isPortrait, $size->isPortrait());
    }

    public function provideOrientation(): array
    {
        return [
            // width, height, isLandscape, isPortrait
            'square' => [100, 100, true, true],
            'landscape' => [200, 100, true, false],
            'portrait' => [100, 200, false, true],
        ];
    }

    public function testShouldConvertImageSizeToJson(): void
    {
        $size = new ImageSize(200, 300);

        $this->assertJsonSerializable(['width' => 200, 'height' => 300], $size);
    }
}
