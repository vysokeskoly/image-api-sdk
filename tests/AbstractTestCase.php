<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use VysokeSkoly\ImageApi\Sdk\ValueObject\Image;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageInterface;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImagePath;
use VysokeSkoly\ImageApi\Sdk\ValueObject\LazyImage;

abstract class AbstractTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function imagePath(string $imageName): ImagePath
    {
        return new ImagePath(__DIR__ . '/Fixtures/' . $imageName);
    }

    protected function image(string $imageName, bool $lazy): ImageInterface
    {
        return $lazy
            ? new LazyImage($this->imagePath($imageName))
            : Image::loadFromPath($this->imagePath($imageName));
    }

    public function provideLaziness(): array
    {
        return [
            // isLazy
            'lazy' => [true],
            'eager' => [false],
        ];
    }

    /** @param mixed $expectedDecodedResult */
    protected function assertJsonSerializable($expectedDecodedResult, \JsonSerializable $serializable): void
    {
        $decoded = json_decode((string) json_encode($serializable), true);

        $this->assertSame($expectedDecodedResult, $decoded);
    }

    /**
     * @param mixed $stringable
     * @todo - use \Stringable interface type
     */
    protected function assertStringable(string $expected, $stringable): void
    {
        $this->assertSame($expected, (string) $stringable);
    }

    protected function assertUploadImageStream(ImageInterface $expectedImage, StreamInterface $stream): void
    {
        $contents = $stream->getContents();

        $this->assertStringContainsString('Content-Disposition: form-data;', $contents);
        $this->assertStringContainsString(sprintf('name="%s";', $expectedImage->getPath()->getFilename()), $contents);
        $this->assertStringContainsString(sprintf('filename="%s"', $expectedImage->getHash()->getHash()), $contents);
        $this->assertStringContainsString(
            $expectedImage->getContent()->getContent(),
            $contents,
            'Stream contents does not contain an expected image content.'
        );
    }
}
