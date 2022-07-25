<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Command;

use Lmc\Cqrs\Http\Command\AbstractHttpDeleteCommand;
use Psr\Http\Message\RequestFactoryInterface;
use VysokeSkoly\ImageApi\Sdk\ValueObject\Api;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageHash;

class DeleteImageCommand extends AbstractHttpDeleteCommand
{
    public function __construct(RequestFactoryInterface $requestFactory, private Api $api, private ImageHash $imageHash)
    {
        parent::__construct($requestFactory);
    }

    public function getUri(): string
    {
        return $this->api->createUrl(sprintf('/image/%s', $this->imageHash));
    }
}
