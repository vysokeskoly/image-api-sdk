<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Service;

use VysokeSkoly\ImageApi\Sdk\ValueObject\Api;

class ApiProvider
{
    private Api $api;

    public function __construct(string $apiUrl, string $apiKey, ?string $namespace = null)
    {
        $this->api = new Api($apiUrl, $apiKey, empty($namespace) ? null : $namespace);
    }

    public function getImageApi(): Api
    {
        return $this->api;
    }
}
