<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Service;

use VysokeSkoly\ImageApi\Sdk\AbstractTestCase;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\InvalidMimeTypeException;
use VysokeSkoly\ImageApi\Sdk\Exception\TooBigImageFileSizeException;
use VysokeSkoly\ImageApi\Sdk\Exception\TooSmallImageException;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageSize;

class ImageValidatorTest extends AbstractTestCase
{
    /** @dataProvider provideLaziness */
    public function testShouldValidateImage(bool $lazy): void
    {
        $image = $this->image('bruce.jpg', $lazy);
        $imageValidator = new ImageValidator(['JPG' => 'image/jpeg'], 10000);

        $imageValidator->assertValidImage($image, new ImageSize(100, 100));

        $this->assertTrue(true);
    }

    /** @dataProvider provideLaziness */
    public function testShouldValidateMimeType(bool $lazy): void
    {
        $image = $this->image('bruce.jpg', $lazy);

        $imageValidator = new ImageValidator(['JPG' => 'image/jpeg'], 666);
        $imageValidator->assertImageMimeType($image);

        $this->assertEquals(new ImageSize(100, 132), $image->getSize());
        $this->assertSame('image/jpeg', $image->getMimeType());
    }

    /** @dataProvider provideLaziness */
    public function testShouldThrowInvalidMimeTypeException(bool $lazy): void
    {
        $image = $this->image('bruce.jpg', $lazy);
        $imageValidator = new ImageValidator(['GIF' => 'image/gif'], 666);

        $this->expectException(InvalidMimeTypeException::class);
        $imageValidator->assertImageMimeType($image);
    }

    /** @dataProvider provideLaziness */
    public function testShouldThrowImageException(bool $lazy): void
    {
        $this->expectException(ImageException::class);

        $this->image('invalid', $lazy);
    }

    /** @dataProvider provideLaziness */
    public function testShouldThrowTooBigException(bool $lazy): void
    {
        $tooBigImage = $this->image('bruce.jpg', $lazy);
        $imageValidator = new ImageValidator(['JPG' => 'image/jpeg'], 1);

        $this->expectException(TooBigImageFileSizeException::class);
        $imageValidator->assertValidImage($tooBigImage, new ImageSize(100, 100));
    }

    /** @dataProvider provideLaziness */
    public function testShouldThrowTooSmallImageException(bool $lazy): void
    {
        $tooSmallImage = $this->image('bruce.jpg', $lazy);
        $imageValidator = new ImageValidator(['JPG' => 'image/jpeg'], 10000);

        $this->expectException(TooSmallImageException::class);
        $imageValidator->assertValidImage($tooSmallImage, new ImageSize(1000, 1000));
    }
}
