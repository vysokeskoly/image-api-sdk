<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Query;

use Lmc\Cqrs\Http\Query\AbstractHttpGetQuery;
use Lmc\Cqrs\Types\ValueObject\CacheTime;
use Psr\Http\Message\RequestFactoryInterface;
use VysokeSkoly\ImageApi\Sdk\ValueObject\Api;

class ListImagesQuery extends AbstractHttpGetQuery
{
    private Api $api;

    public function __construct(RequestFactoryInterface $requestFactory, Api $api)
    {
        parent::__construct($requestFactory);
        $this->api = $api;
    }

    public function getUri(): string
    {
        return $this->api->createUrl('/list/');
    }

    public function getCacheTime(): CacheTime
    {
        return CacheTime::noCache();
    }
}
