<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Service;

use PHPUnit\Framework\TestCase;
use VysokeSkoly\ImageApi\Sdk\ValueObject\Crop;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageContent;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImagePath;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageSize;

class ImageGeneratorTest extends TestCase
{
    private ImageGenerator $imageGenerator;

    protected function setUp(): void
    {
        $this->imageGenerator = new ImageGenerator();
    }

    /** @dataProvider provideImageToGenerate */
    public function testShouldGenerateImage(
        ImagePath $expectedPath,
        ImagePath $path,
        ImageSize $size,
        ?Crop $crop,
    ): void {
        $generated = $this->imageGenerator->generate(ImageContent::loadFromPath($path), $size, $crop);
        $generatedPath = str_replace($path->getFilename(), 'generated-' . $expectedPath->getFilename(), $path->getPath());
        file_put_contents($generatedPath, $generated->getContent());

        // check expected size
        $actual = new \Imagick($generatedPath);
        $this->assertEquals($size, ImageSize::createFromImagick($actual));

        // compare generated image with pre-generated example
        $expected = new \Imagick($expectedPath->getPath());
        $result = $expected->compareImages($actual, \Imagick::METRIC_MEANSQUAREERROR);
        $this->assertLessThanOrEqual(10.0, $result[1]);
    }

    public function provideImageToGenerate(): array
    {
        $fixture = fn (string $name) => new ImagePath(sprintf(__DIR__ . '/../Fixtures/%s', $name));

        return [
            // expectedPath, imagePath, size, crop
            '1:1 - no crop' => [
                $fixture('500x300.png'),
                $fixture('500x300.png'),
                new ImageSize(500, 300),
                null,
            ],
            'smaller by crop' => [
                $fixture('smaller-by-crop.png'),
                $fixture('500x300.png'),
                new ImageSize(500, 300),
                Crop::parse(['x' => 50, 'y' => 50, 'x2' => 450, 'y2' => 250]),
            ],
            '50% - no crop' => [
                $fixture('250x150.png'),
                $fixture('500x300.png'),
                new ImageSize(250, 150),
                null,
            ],
            '50x30' => [
                $fixture('50x30.png'),
                $fixture('500x300.png'),
                new ImageSize(50, 30),
                null,
            ],
            'smaller by crop and to 50x30' => [
                $fixture('50x30-crop-100-50-250x150.png'),
                $fixture('500x300.png'),
                new ImageSize(50, 30),
                Crop::parse(['x' => 50, 'y' => 50, 'x2' => 450, 'y2' => 250]),
            ],
            'bigbruce - crop and resize' => [
                $fixture('bigbruce-thumbnail.jpg'),
                $fixture('bigbruce.jpg'),
                new ImageSize(60, 90),
                Crop::parse(['x' => 16, 'y' => 5, 'x2' => 185, 'y2' => 240]),
            ],
        ];
    }
}
