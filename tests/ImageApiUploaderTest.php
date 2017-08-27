<?php

namespace VysokeSkoly\Tests\ImageApi\Sdk;

use Mockery as m;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\UnableToLoadImageException;
use VysokeSkoly\ImageApi\Sdk\Service\ApiService;
use VysokeSkoly\ImageApi\Sdk\Service\ImageFactory;
use VysokeSkoly\Tests\ImageApi\Sdk\Fixtures\TestableImageApiUploader;

class ImageApiUploaderTest extends AbstractTestCase
{
    const IMAGE_URL = 'imageUrl/';

    /** @var TestableImageApiUploader */
    private $imageApiUploader;

    /** @var ApiService|m\MockInterface */
    private $apiService;

    /** @var ImageFactory|m\MockInterface */
    private $imageFactory;

    public function setUp()
    {
        $this->checkGmagick();

        $this->apiService = m::spy(ApiService::class);
        $this->imageFactory = m::mock(ImageFactory::class);

        $this->imageApiUploader = new TestableImageApiUploader(
            ['JPG' => 'image/jpeg'],
            2 * 1024 * 1024,
            200,
            self::IMAGE_URL,
            'apiUrl',
            'apiKey'
        );

        $this->imageApiUploader->setApiService($this->apiService);

        if (!self::$isGmagickEnabled) {
            $this->imageApiUploader->setImageFactory($this->imageFactory);
        }
    }

    public function testShouldValidateAndUploadImage()
    {
        $imagePath = __DIR__ . '/Fixtures/bruce.jpg';
        $width = 100;
        $height = 132;

        $imageData = file_get_contents($imagePath);
        $expectedHash = sha1($imageData);
        $expectedUrl = self::IMAGE_URL . $expectedHash . '/';

        $expectedResult = ['url' => $expectedUrl, 'hash' => $expectedHash, 'width' => $width, 'height' => $height];

        if (!self::$isGmagickEnabled) {
            $this->mockImageFactory($height, $width, $imageData);
        }

        $result = $this->imageApiUploader->validateAndUpload($imagePath, $width, $height);

        $this->apiService->shouldHaveReceived('saveString')
            ->with($imageData, $expectedHash)
            ->once();

        $this->assertSame($expectedResult, $result->toArray());
    }

    private function mockImageFactory(int $height, int $width, string $imageData): void
    {
        $image = new \Gmagick();
        $image->setHeight($height);
        $image->setWidth($width);
        $image->readimageblob($imageData);

        $this->imageFactory->shouldReceive('createImage')
            ->with($imageData)
            ->once()
            ->andReturn($image);
    }

    public function testShouldValidateAndUploadImageWithRatio()
    {
        $imagePath = __DIR__ . '/Fixtures/bruce.jpg';
        $width = 100;
        $height = 132;
        $ratio = 0.5;

        $imageData = file_get_contents($imagePath);
        $expectedHash = sha1($imageData);
        $expectedUrl = self::IMAGE_URL . $expectedHash . '/';

        $expectedResult = [
            'url' => $expectedUrl,
            'hash' => $expectedHash,
            'width' => $width,
            'height' => $height,
            'crop_topleft_x' => 17,
            'crop_topleft_y' => 0,
            'crop_bottomright_x' => 83,
            'crop_bottomright_y' => 132,
        ];

        if (!self::$isGmagickEnabled) {
            $this->mockImageFactory($height, $width, $imageData);
        }

        $result = $this->imageApiUploader->validateAndUpload($imagePath, $width, $height, $ratio);

        $this->apiService->shouldHaveReceived('saveString')
            ->with($imageData, $expectedHash)
            ->once();

        $this->assertSame($expectedResult, $result->toArray());
    }

    public function testShouldValidateAndUploadImageWithResizing()
    {
        $imagePath = __DIR__ . '/Fixtures/bigbruce.jpg';
        $width = 214;
        $height = 317;

        $imageData = file_get_contents($imagePath);
        $expectedHash = sha1($imageData);
        $expectedUrl = self::IMAGE_URL . $expectedHash . '/';

        $expectedResult = ['url' => $expectedUrl, 'hash' => $expectedHash, 'width' => $width, 'height' => $height];

        if (!self::$isGmagickEnabled) {
            $this->mockImageFactory($height, $width, $imageData);
        }

        $result = $this->imageApiUploader->validateAndUpload($imagePath, $width, $height);

        $this->apiService->shouldHaveReceived('saveString')
            ->with($imageData, $expectedHash)
            ->once();

        $this->assertSame($expectedResult, $result->toArray());
    }

    public function testShouldUploadImage()
    {
        $imagePath = __DIR__ . '/Fixtures/bruce.jpg';
        $width = 100;
        $height = 132;

        $imageData = file_get_contents($imagePath);
        $expectedHash = sha1($imageData);
        $expectedUrl = self::IMAGE_URL . $expectedHash . '/';

        $expectedResult = ['url' => $expectedUrl, 'hash' => $expectedHash, 'width' => $width, 'height' => $height];

        $this->imageApiUploader->setImageFactory($this->imageFactory);

        $result = $this->imageApiUploader->upload($imagePath);

        $this->imageFactory->shouldNotHaveReceived('createImage');
        $this->apiService->shouldHaveReceived('saveString')
            ->with($imageData, $expectedHash)
            ->once();

        $this->assertSame($expectedResult, $result->toArray());
    }

    public function testShouldThrowImageException()
    {
        $this->expectException(ImageException::class);
        $invalidImagePath = 'invalid';

        $this->imageApiUploader->validateAndUpload($invalidImagePath, 100, 100);
    }

    public function testShouldThrowUnableToLoadException()
    {
        $this->expectException(UnableToLoadImageException::class);
        $emptyImagePath = __DIR__ . '/Fixtures/empty.jpg';

        $this->imageApiUploader->upload($emptyImagePath);
    }

    public function testShouldThrowImageExceptionOnGmagickException()
    {
        $this->expectException(ImageException::class);

        $this->imageFactory->shouldReceive('createImage')
            ->andThrow(new \Exception('Some Gmagick exception'));

        $this->imageApiUploader->setImageFactory($this->imageFactory);

        $imagePath = __DIR__ . '/Fixtures/bruce.jpg';

        $this->imageApiUploader->validateAndUpload($imagePath, 100, 100);
    }

    public function testShouldDeleteImage()
    {
        $imageName = 'image-to-delete';

        $this->imageApiUploader->delete($imageName);

        $this->apiService->shouldHaveReceived('delete')
            ->with($imageName)
            ->once();

        $this->assertTrue(true);
    }
}
