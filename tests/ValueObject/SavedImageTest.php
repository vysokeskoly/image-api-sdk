<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use VysokeSkoly\ImageApi\Sdk\AbstractTestCase;

class SavedImageTest extends AbstractTestCase
{
    public function testShouldCreateSavedImage(): void
    {
        $image = $this->image('bruce.jpg', false);
        $savedImage = SavedImage::createFromImage('https://cdn/', $image);

        $this->assertSame($image->getHash(), $savedImage->getHash());
        $this->assertEquals($image->getSize(), $savedImage->getSize());
    }

    /** @dataProvider provideBaseUrl */
    public function testShouldCreateValidUrl(string $baseUrl, string $expectedUrl): void
    {
        $image = $this->image('bruce.jpg', false);
        $savedImage = SavedImage::createFromImage($baseUrl, $image);

        $this->assertSame($expectedUrl, $savedImage->getUrl());
    }

    public function provideBaseUrl(): array
    {
        $hash = ImageHashTest::HASH_BRUCE;

        return [
            // baseUrl, expectedUrl
            'empty' => ['', "/$hash/"],
            'with trailing slash' => ['https://cdn/', "https://cdn/$hash/"],
            'without trailing slash' => ['https://cdn', "https://cdn/$hash/"],
        ];
    }

    public function testShouldConvertSavedImageToJson(): void
    {
        $image = $this->image('bruce.jpg', false);
        $savedImage = SavedImage::createFromImage('https://cdn/', $image);
        $expected = [
            'url' => 'https://cdn/' . ImageHashTest::HASH_BRUCE . '/',
            'hash' => ImageHashTest::HASH_BRUCE,
            'width' => 100,
            'height' => 132,
        ];

        $this->assertJsonSerializable($expected, $savedImage);
    }
}
