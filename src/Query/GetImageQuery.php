<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Query;

use Lmc\Cqrs\Http\Query\AbstractHttpGetQuery;
use Lmc\Cqrs\Types\ValueObject\CacheTime;
use Psr\Http\Message\RequestFactoryInterface;
use VysokeSkoly\ImageApi\Sdk\ValueObject\Api;
use VysokeSkoly\ImageApi\Sdk\ValueObject\ImageHash;

class GetImageQuery extends AbstractHttpGetQuery
{
    private Api $api;
    private ImageHash $imageHash;

    public function __construct(RequestFactoryInterface $requestFactory, Api $api, ImageHash $imageHash)
    {
        parent::__construct($requestFactory);
        $this->api = $api;
        $this->imageHash = $imageHash;
    }

    public function getUri(): string
    {
        return $this->api->createUrl(sprintf('/image/%s', $this->imageHash));
    }

    public function getCacheTime(): CacheTime
    {
        return CacheTime::noCache();
    }
}
