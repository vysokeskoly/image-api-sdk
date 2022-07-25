<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk;

use Lmc\Cqrs\Types\ValueObject\CacheTime;
use Mockery as m;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use VysokeSkoly\ImageApi\Sdk\Command\DeleteImageCommand;
use VysokeSkoly\ImageApi\Sdk\Command\UploadImageCommand;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Fixtures\UploadCommandHandler;
use VysokeSkoly\ImageApi\Sdk\Query\GetImageQuery;
use VysokeSkoly\ImageApi\Sdk\Query\ListImagesQuery;
use VysokeSkoly\ImageApi\Sdk\Service\ApiProvider;
use VysokeSkoly\ImageApi\Sdk\Service\CommandQueryFactory;
use VysokeSkoly\ImageApi\Sdk\Service\ImagesCache;
use VysokeSkoly\ImageApi\Sdk\Service\SavedImageDecoder;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageHash;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageSize;
use VysokeSkoly\ImageApi\Sdk\ValueObject\SavedImage;

class ImageApiUploaderTest extends AbstractTestCase
{
    private const MAX_IMAGE_SIZE = 200;

    private ImageApiUploader $imageApiUploader;
    /** @var RequestFactoryInterface|m\MockInterface */
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    protected function setUp(): void
    {
        $this->requestFactory = m::mock(RequestFactoryInterface::class);
        $this->streamFactory = new Psr17Factory();

        $commandQueryFactory = new CommandQueryFactory(
            $this->requestFactory,
            $this->streamFactory,
            new ApiProvider('http://api', 'api-key', 'namespace')
        );

        $this->imageApiUploader = new ImageApiUploader(
            ['JPG' => 'image/jpeg'],
            2 * 1024 * 1024,
            self::MAX_IMAGE_SIZE,
            $commandQueryFactory
        );
    }

    /** @dataProvider provideLaziness */
    public function testShouldValidateAndUploadImageAndDecodeResponse(bool $lazy): void
    {
        $image = $this->image('bruce.jpg', $lazy);

        $uploadCommand = $this->imageApiUploader->validateAndUpload($image, new ImageSize(100, 100));

        $this->assertInstanceOf(UploadImageCommand::class, $uploadCommand);
        $this->assertSame('http://api/image/?apikey=api-key&namespace=namespace', $uploadCommand->getUri());
        $this->assertSame('POST', $uploadCommand->getHttpMethod());
        $this->assertUploadImageStream($image, $uploadCommand->createBody());

        $this->assertTrue(ImagesCache::containsHash($image->getHash()));

        // Validate decoded response
        $response = UploadCommandHandler::SUCCESS_RESPONSE;
        $response['messages'][] = $image->getHash()->getHash();
        $savedImageDecoder = new SavedImageDecoder('http://cdn/');
        $this->assertTrue($savedImageDecoder->supports($response, $uploadCommand));
        $savedImages = $savedImageDecoder
            ->decode($response)
            ->getValue();

        $this->assertIsArray($savedImages);
        $this->assertCount(1, $savedImages);
        $savedImage = array_shift($savedImages);
        $this->assertInstanceOf(SavedImage::class, $savedImage);

        $this->assertSame($image->getHash(), $savedImage->getHash());
        $this->assertEquals($image->getSize(), $savedImage->getSize());
        $this->assertSame(sprintf('http://cdn/%s/', $image->getHash()), $savedImage->getUrl());

        // clear cache after "closing" ImageApiUploader
        unset($this->imageApiUploader);
        $this->assertFalse(ImagesCache::containsHash($image->getHash()));
    }

    /** @dataProvider provideLaziness */
    public function testShouldValidateAndUploadImageWithResizing(bool $lazy): void
    {
        $image = $this->image('bigbruce.jpg', $lazy);

        $uploadWithResizingCommand = $this->imageApiUploader->validateAndUpload($image, new ImageSize(100, 100));

        $this->assertInstanceOf(UploadImageCommand::class, $uploadWithResizingCommand);
        $this->assertSame('POST', $uploadWithResizingCommand->getHttpMethod());
        $this->assertSame('http://api/image/?apikey=api-key&namespace=namespace', $uploadWithResizingCommand->getUri());

        $resizedImage = $image->scalePortraitTo(self::MAX_IMAGE_SIZE);
        $this->assertUploadImageStream($resizedImage, $uploadWithResizingCommand->createBody());
    }

    /** @dataProvider provideLaziness */
    public function testShouldUploadImageWithoutResizing(bool $lazy): void
    {
        $image = $this->image('bigbruce.jpg', $lazy);

        // Without resizing
        $uploadWithoutResizingCommand = $this->imageApiUploader->upload($image);

        $this->assertInstanceOf(UploadImageCommand::class, $uploadWithoutResizingCommand);
        $this->assertSame('POST', $uploadWithoutResizingCommand->getHttpMethod());
        $this->assertSame(
            'http://api/image/?apikey=api-key&namespace=namespace',
            $uploadWithoutResizingCommand->getUri()
        );
        $this->assertUploadImageStream($image, $uploadWithoutResizingCommand->createBody());
    }

    /** @dataProvider provideLaziness */
    public function testShouldThrowUnableToLoadException(bool $lazy): void
    {
        if (!$lazy) {
            $this->expectException(ImageException::class);
        }
        $image = $this->image('empty.jpg', $lazy);

        if ($lazy) {
            $this->expectException(ImageException::class);
        }
        $this->imageApiUploader->upload($image);
    }

    public function testShouldDeleteImage(): void
    {
        $imageHash = new ImageHash('image-to-delete');
        $deleteCommand = $this->imageApiUploader->delete($imageHash);

        $this->assertInstanceOf(DeleteImageCommand::class, $deleteCommand);
        $this->assertSame('DELETE', $deleteCommand->getHttpMethod());
        $this->assertSame(
            sprintf('http://api/image/%s?apikey=api-key&namespace=namespace', $imageHash),
            $deleteCommand->getUri()
        );
    }

    public function testShouldListAll(): void
    {
        $listQuery = $this->imageApiUploader->listAllImageNames();

        $this->assertInstanceOf(ListImagesQuery::class, $listQuery);
        $this->assertSame('GET', $listQuery->getHttpMethod());
        $this->assertSame('http://api/list/?apikey=api-key&namespace=namespace', $listQuery->getUri());
        $this->assertEquals(CacheTime::noCache(), $listQuery->getCacheTime());
    }

    public function testShouldGetImage(): void
    {
        $imageHash = new ImageHash('image');
        $getImageQuery = $this->imageApiUploader->get($imageHash);

        $this->assertInstanceOf(GetImageQuery::class, $getImageQuery);
        $this->assertSame('GET', $getImageQuery->getHttpMethod());
        $this->assertSame(
            sprintf('http://api/image/%s?apikey=api-key&namespace=namespace', $imageHash),
            $getImageQuery->getUri()
        );
        $this->assertEquals(CacheTime::noCache(), $getImageQuery->getCacheTime());
    }

    /** @dataProvider provideLaziness */
    public function testShouldUseCacheOnUpload(bool $lazy): void
    {
        $image = $this->image('bruce.jpg', $lazy);

        $this->imageApiUploader->enableCache();
        $uploadCommand = $this->imageApiUploader->validateAndUpload($image, new ImageSize(100, 100));

        $this->assertInstanceOf(UploadImageCommand::class, $uploadCommand);
        $this->assertSame('http://api/image/?apikey=api-key&namespace=namespace', $uploadCommand->getUri());
        $this->assertSame('POST', $uploadCommand->getHttpMethod());
        $this->assertUploadImageStream($image, $uploadCommand->createBody());

        $this->assertTrue(ImagesCache::containsHash($image->getHash()));

        ImagesCache::disable();
        $this->assertFalse(ImagesCache::containsHash($image->getHash()));
    }
}
