<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use VysokeSkoly\ImageApi\Sdk\AbstractTestCase;

class ImageTest extends AbstractTestCase
{
    public function testShouldLoadImageFromPath(): void
    {
        $path = $this->imagePath('bruce.jpg');
        $image = Image::loadFromPath($path);

        $this->assertSame($path, $image->getPath());
        $this->assertSame(ImageHashTest::HASH_BRUCE, $image->getHash()->getHash());
        $this->assertEquals(new ImageSize(100, 132), $image->getSize());
        $this->assertSame('image/jpeg', $image->getMimeType());
        $this->assertSame(9695, $image->getFileSize());
        $this->assertSame((string) file_get_contents($path->getPath()), $image->getContent()->getContent());
    }

    public function testShouldScaleLoadedImage(): void
    {
        $path = $this->imagePath('bruce.jpg');
        $image = Image::loadFromPath($path);

        $this->assertTrue($image->getSize()->isPortrait());
        $scaledUp = $image->scalePortraitTo(150);
        $this->assertEquals(new ImageSize(114, 150), $scaledUp->getSize());

        $scaledDown = $image->scalePortraitTo(100);
        $this->assertEquals(new ImageSize(76, 100), $scaledDown->getSize());

        $scaledToSize = $image->scaleTo(new ImageSize(200, 300));
        $this->assertEquals(new ImageSize(200, 264), $scaledToSize->getSize());

        $scaledAsLandscape = $image->scaleLandscapeTo(150);
        $this->assertEquals(new ImageSize(150, 198), $scaledAsLandscape->getSize());
    }
}
