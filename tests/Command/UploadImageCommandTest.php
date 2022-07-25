<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Command;

use Lmc\Cqrs\Handler\CommandSender;
use Lmc\Cqrs\Types\CommandSenderInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use VysokeSkoly\ImageApi\Sdk\AbstractTestCase;
use VysokeSkoly\ImageApi\Sdk\Fixtures\UploadCommandHandler;
use VysokeSkoly\ImageApi\Sdk\Service\ImagesCache;
use VysokeSkoly\ImageApi\Sdk\Service\SavedImageDecoder;
use VysokeSkoly\ImageApi\Sdk\ValueObject\Api;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageSize;
use VysokeSkoly\ImageApi\Sdk\ValueObject\SavedImage;

class UploadImageCommandTest extends AbstractTestCase
{
    /** @phpstan-var CommandSenderInterface<mixed, mixed> */
    private CommandSenderInterface $commandSender;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    protected function setUp(): void
    {
        $imageUrl = 'http://cdn/';

        $this->requestFactory = $this->streamFactory = new Psr17Factory();

        $this->commandSender = new CommandSender(
            null,
            [new UploadCommandHandler()],
            [new SavedImageDecoder($imageUrl)]
        );
    }

    /** @dataProvider provideLaziness */
    public function testShouldCreateRequest(bool $lazy): void
    {
        $image = $this->image('bruce.jpg', $lazy);

        $command = new UploadImageCommand(
            $this->requestFactory,
            $this->streamFactory,
            new Api('http://api/', 'key', null),
            $image
        );

        $request = $command->createRequest();

        $this->assertSame('http://api/image/?apikey=key', (string) $request->getUri());
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringStartsWith('multipart/form-data; boundary="', $request->getHeader('Content-Type')[0]);
    }

    /** @dataProvider provideLaziness */
    public function testShouldSendUploadImageCommand(bool $lazy): void
    {
        $image = $this->image('bruce.jpg', $lazy);
        ImagesCache::enable();
        ImagesCache::storeImage($image);    // NOTE: this is normally done in the ImageApiUploader

        $command = new UploadImageCommand(
            $this->requestFactory,
            $this->streamFactory,
            new Api('http://api/', 'key', null),
            $image
        );

        $savedImages = $this->commandSender->sendAndReturn($command);
        $this->assertFalse(ImagesCache::containsHash($image->getHash()));

        $this->assertIsArray($savedImages);
        $this->assertCount(1, $savedImages);
        $savedImage = array_shift($savedImages);
        $this->assertInstanceOf(SavedImage::class, $savedImage);

        $this->assertEquals($image->getHash(), $savedImage->getHash());
        $this->assertEquals($image->getSize(), $savedImage->getSize());
        $this->assertSame(sprintf('http://cdn/%s/', $image->getHash()), $savedImage->getUrl());
    }

    /** @dataProvider provideLaziness */
    public function testShouldSendUploadImageCommandWithoutCache(bool $lazy): void
    {
        $image = $this->image('bruce.jpg', $lazy);

        $command = new UploadImageCommand(
            $this->requestFactory,
            $this->streamFactory,
            new Api('http://api/', 'key', null),
            $image
        );

        $savedImages = $this->commandSender->sendAndReturn($command);

        $this->assertIsArray($savedImages);
        $this->assertCount(1, $savedImages);
        $savedImage = array_shift($savedImages);
        $this->assertInstanceOf(SavedImage::class, $savedImage);

        $this->assertEquals($image->getHash(), $savedImage->getHash());
        $this->assertEquals(ImageSize::empty(), $savedImage->getSize());
        $this->assertSame(sprintf('http://cdn/%s/', $image->getHash()), $savedImage->getUrl());
    }
}
