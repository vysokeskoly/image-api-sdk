<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Service;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use VysokeSkoly\ImageApi\Sdk\Command\DeleteImageCommand;
use VysokeSkoly\ImageApi\Sdk\Command\UploadImageCommand;
use VysokeSkoly\ImageApi\Sdk\Query\GetImageQuery;
use VysokeSkoly\ImageApi\Sdk\Query\ListImagesQuery;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageHash;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageInterface;

class CommandQueryFactory
{
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private ApiProvider $apiProvider;

    public function __construct(
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        ApiProvider $apiProvider
    ) {
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->apiProvider = $apiProvider;
    }

    public function createUploadCommand(ImageInterface $image): UploadImageCommand
    {
        return new UploadImageCommand(
            $this->requestFactory,
            $this->streamFactory,
            $this->apiProvider->getImageApi(),
            $image
        );
    }

    public function createDeleteCommand(ImageHash $hash): DeleteImageCommand
    {
        return new DeleteImageCommand($this->requestFactory, $this->apiProvider->getImageApi(), $hash);
    }

    public function createGetQuery(ImageHash $hash): GetImageQuery
    {
        return new GetImageQuery($this->requestFactory, $this->apiProvider->getImageApi(), $hash);
    }

    public function createListQuery(): ListImagesQuery
    {
        return new ListImagesQuery($this->requestFactory, $this->apiProvider->getImageApi());
    }
}
