<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

class Api
{
    private string $apiUrl;

    public function __construct(string $apiUrl, private string $apiKey, private ?string $namespace)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function createUrl(string $path): string
    {
        return sprintf(
            '%s/%s?apikey=%s%s',
            $this->getApiUrl(),
            ltrim($path, '/'),
            $this->getApiKey(),
            $this->getNamespace() ? sprintf('&namespace=%s', trim($this->getNamespace(), ' /')) : '',
        );
    }
}
