<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use VysokeSkoly\ImageApi\Sdk\AbstractTestCase;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;

class LazyImageTest extends AbstractTestCase
{
    /** @dataProvider provideGetter */
    public function testShouldGetDataFromLoadedLazyFile(string $method): void
    {
        $path = $this->imagePath('bruce.jpg');
        $image = new LazyImage($path);
        $this->assertSame($path, $image->getPath());

        $image->load();

        $data = $image->{$method}();
        $this->assertNotNull($data);
    }

    public function testShouldNotLoadFromDeletedFile(): void
    {
        $path = $this->prepareLazyImage('bruce.jpg');
        $image = new LazyImage($path);
        $this->assertSame($path, $image->getPath());

        $this->expectException(ImageException::class);
        $image->load();
    }

    /** @dataProvider provideGetter */
    public function testShouldGetDataFromLazyFile(string $method): void
    {
        $path = $this->imagePath('bruce.jpg');
        $image = new LazyImage($path);

        $data = $image->{$method}();
        $this->assertNotNull($data);
    }

    /** @dataProvider provideGetter */
    public function testShouldNotGetDataFromDeletedFile(string $method): void
    {
        $path = $this->prepareLazyImage('bruce.jpg');
        $image = new LazyImage($path);

        $this->expectException(ImageException::class);
        $image->{$method}();
    }

    private function prepareLazyImage(string $imageName): ImagePath
    {
        $path = $this->imagePath($imageName);
        $preparedPath = str_replace($imageName, 'copy-' . $imageName, $path->getPath());

        // copy image as a lazy one
        copy($path->getPath(), $preparedPath);

        $preparedImagePath = new ImagePath($preparedPath);
        unlink($preparedPath);

        return $preparedImagePath;
    }

    public function provideGetter(): array
    {
        return [
            // getterMethod
            'content' => ['getContent'],
            'hash' => ['getHash'],
            'size' => ['getSize'],
            'mimeType' => ['getMimeType'],
            'mimeFileSize' => ['getFileSize'],
        ];
    }

    public function testShouldLoadGifAsPng(): void
    {
        $path = $this->imagePath('homer.gif');
        $image = new LazyImage($path);

        $image->load();
        $this->assertSame('image/png', $image->getMimeType());
    }

    public function testShouldScaleLoadedImage(): void
    {
        $path = $this->imagePath('bruce.jpg');
        $image = new LazyImage($path);

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

    public function testShouldThrowImageExceptionOnLoadEmptyImage(): void
    {
        $path = $this->imagePath('empty.jpg');

        $image = new LazyImage($path);

        $this->expectException(ImageException::class);
        $image->load();
    }

    /** @dataProvider provideGetter */
    public function testShouldThrowImageExceptionOnGetImageData(string $method): void
    {
        $path = $this->imagePath('empty.jpg');

        $image = new LazyImage($path);

        $this->expectException(ImageException::class);
        $image->{$method}();
    }
}
