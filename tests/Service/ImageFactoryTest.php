<?php

namespace VysokeSkoly\Tests\ImageApi\Sdk\Service;

use VysokeSkoly\ImageApi\Sdk\Service\ImageFactory;
use VysokeSkoly\Tests\ImageApi\Sdk\AbstractTestCase;

class ImageFactoryTest extends AbstractTestCase
{
    /** @var ImageFactory */
    private $imageFactory;

    public function setUp()
    {
        $this->checkGmagick();

        $this->imageFactory = new ImageFactory();
    }

    public function testShouldCreateGmagick()
    {
        $imageData = file_get_contents(__DIR__ . '/../Fixtures/bruce.jpg');

        $image = $this->imageFactory->createImage($imageData);

        $this->assertInstanceOf(\Gmagick::class, $image);
    }

    public function testShouldCreatePngFromGif()
    {
        if (!self::$isGmagickEnabled) {
            $backup = \Gmagick::$imageFormat;
            \Gmagick::$imageFormat = 'GIF';
        }
        $expectedFormat = 'PNG';
        $imageData = file_get_contents(__DIR__ . '/../Fixtures/homer.gif');

        $image = $this->imageFactory->createImage($imageData);

        $this->assertInstanceOf(\Gmagick::class, $image);
        $this->assertSame($expectedFormat, $image->getImageFormat());

        if (!self::$isGmagickEnabled) {
            \Gmagick::$imageFormat = $backup ?? 'JPG';
        }
    }
}
