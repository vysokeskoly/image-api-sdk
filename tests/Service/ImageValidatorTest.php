<?php

namespace VysokeSkoly\ImageApi\Sdk\Service;

use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\InvalidMimeTypeException;
use VysokeSkoly\ImageApi\Sdk\Exception\TooBigImageFileSizeException;
use VysokeSkoly\ImageApi\Sdk\Exception\TooSmallImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\UnableToLoadImageException;
use VysokeSkoly\ImageApi\Sdk\Service\ImageValidator;
use VysokeSkoly\ImageApi\Sdk\AbstractTestCase;

class ImageValidatorTest extends AbstractTestCase
{
    public function testShouldValidateImage()
    {
        $image = __DIR__ . '/../Fixtures/bruce.jpg';
        $imageValidator = new ImageValidator(['JPG' => 'image/jpeg'], 10000);

        $imageValidator->assertValidImage($image, 100, 100);

        $this->assertTrue(true);
    }

    public function testShouldValidateMimeType()
    {
        $image = __DIR__ . '/../Fixtures/bruce.jpg';
        $expectedImageInfo = [
            0 => 100,
            1 => 132,
            2 => 2,
            3 => 'width="100" height="132"',
            'bits' => 8,
            'channels' => 3,
            'mime' => 'image/jpeg',
        ];
        $imageValidator = new ImageValidator(['JPG' => 'image/jpeg'], 666);

        $imageInfo = $imageValidator->assertImageMimeType($image);

        $this->assertSame($expectedImageInfo, $imageInfo);
    }

    public function testShouldThrowInvalidMimeTypeException()
    {
        $this->expectException(InvalidMimeTypeException::class);

        $image = __DIR__ . '/../Fixtures/bruce.jpg';
        $imageValidator = new ImageValidator(['GIF' => 'image/gif'], 666);

        $imageValidator->assertImageMimeType($image);
    }

    public function testShouldThrowImageException()
    {
        $this->expectException(ImageException::class);
        $invalidImagePath = 'invalid';

        $imageValidator = new ImageValidator(['JPG' => 'image/jpeg'], 666);

        $imageValidator->assertValidImage($invalidImagePath, 100, 100);
    }

    public function testShouldThrowUnableToLoadException()
    {
        $this->expectException(UnableToLoadImageException::class);

        $emptyImagePath = __DIR__ . '/../Fixtures/empty.jpg';
        $imageValidator = new ImageValidator(['JPG' => 'image/jpeg'], 666);

        $imageValidator->assertValidImage($emptyImagePath, 100, 100);
    }

    public function testShouldThrowTooBigException()
    {
        $this->expectException(TooBigImageFileSizeException::class);

        $tooBigImage = __DIR__ . '/../Fixtures/bruce.jpg';
        $imageValidator = new ImageValidator(['JPG' => 'image/jpeg'], 1);

        $imageValidator->assertValidImage($tooBigImage, 100, 100);
    }

    public function testShouldThrowTooSmallImageException()
    {
        $this->expectException(TooSmallImageException::class);

        $tooSmallImage = __DIR__ . '/../Fixtures/bruce.jpg';
        $imageValidator = new ImageValidator(['JPG' => 'image/jpeg'], 10000);

        $imageValidator->assertValidImage($tooSmallImage, 1000, 1000);
    }
}
