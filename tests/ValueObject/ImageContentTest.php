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

    /** @dataProvider provideContentType */
    public function testShouldParseImageType(ImageContent $content, ?string $expected): void
    {
        $type = $content->parseRealImageType();
        $this->assertSame($expected, $type);
    }

    public function provideContentType(): array
    {
        $image = fn (string $name) => ImageContent::loadFromPath(
            new ImagePath(sprintf(__DIR__ . '/../Fixtures/%s', $name)),
        );

        return [
            // content, realExtension
            'invalid' => [new ImageContent('string'), null],
            'jpg' => [$image('image.jpg'), 'jpg'],
            'png' => [$image('image.png'), 'png'],
            'gif' => [$image('image.gif'), 'gif'],
        ];
    }
}
