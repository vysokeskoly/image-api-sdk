<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Command;

use Http\Message\MultipartStream\MultipartStreamBuilder;
use Lmc\Cqrs\Http\Command\AbstractHttpPostCommand;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use VysokeSkoly\ImageApi\Sdk\ValueObject\Api;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageHash;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageInterface;

class UploadImageCommand extends AbstractHttpPostCommand
{
    private ?MultipartStreamBuilder $builder = null;

    public function __construct(
        RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private Api $api,
        private ImageInterface $image,
    ) {
        parent::__construct($requestFactory);
    }

    public function getUri(): string
    {
        return $this->api->createUrl('/image/');
    }

    public function createBody(): StreamInterface
    {
        return $this->buildBody()->build();
    }

    private function buildBody(): MultipartStreamBuilder
    {
        if ($this->builder === null) {
            $this->builder = new MultipartStreamBuilder($this->streamFactory);
            $this->builder->addResource(
                $this->image->getPath()->getFilename(),
                $this->image->asStream($this->streamFactory),
                [
                    'filename' => $this->image->getHash()->getHash(),
                ],
            );
        }

        return $this->builder;
    }

    public function modifyRequest(RequestInterface $request): RequestInterface
    {
        $builder = $this->buildBody();

        return $request
            ->withHeader('Content-Type', 'multipart/form-data; boundary="' . $builder->getBoundary() . '"');
    }

    public function getImageHash(): ImageHash
    {
        return $this->image->getHash();
    }
}
