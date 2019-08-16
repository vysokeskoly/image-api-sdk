<?php

namespace VysokeSkoly\ImageApi\Sdk\Service;

use Assert\Assertion;
use Assert\InvalidArgumentException;
use GuzzleHttp\Client;
use VysokeSkoly\ImageApi\Sdk\Exception\ApiException;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;

class ApiService
{
    /** @var Client */
    private $client;
    /** @var string */
    private $apiUrl;
    /** @var string */
    private $apiKey;
    /** @var string|null */
    private $namespace;

    public function __construct(Client $client, string $apiUrl, string $apiKey)
    {
        $this->client = $client;
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    public function saveString(string $content, string $fileName): void
    {
        try {
            Assertion::notEmpty($content);
            Assertion::notEmpty($fileName);

            $this->postImage('/image/', $content, $fileName);
        } catch (InvalidArgumentException $e) {
            throw ImageException::from($e);
        }
    }

    private function postImage(string $endpoint, string $content, string $fileName): void
    {
        $res = $this->client->request(
            'POST',
            $this->getApiUrl($endpoint),
            [
                'multipart' => [
                    [
                        'name' => $fileName,
                        'contents' => $content,
                        'filename' => $fileName,
                    ],
                ],
            ]
        );

        if ($res->getStatusCode() >= 400) {
            throw ApiException::create($res->getStatusCode(), $res->getBody()->getContents());
        }
    }

    private function getApiUrl(string $endpoint): string
    {
        return $this->apiUrl . $endpoint . $this->getAuth() . $this->addNamespace();
    }

    private function getAuth(): string
    {
        return '?apikey=' . $this->apiKey;
    }

    private function addNamespace(): string
    {
        return $this->namespace !== null
            ? sprintf('&namespace=%s', trim($this->namespace, ' /'))
            : '';
    }

    public function delete(string $fileName)
    {
        try {
            Assertion::notEmpty($fileName);

            $this->deleteImage('/image/' . $fileName);
        } catch (InvalidArgumentException $e) {
            throw ImageException::from($e);
        }
    }

    private function deleteImage(string $endpoint): void
    {
        $res = $this->client->request('DELETE', $this->getApiUrl($endpoint));

        if ($res->getStatusCode() >= 400) {
            throw ApiException::create($res->getStatusCode(), $res->getBody()->getContents());
        }
    }

    public function listAll(): array
    {
        try {
            return $this->getResultDecoded('/list/');
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ApiException::from($e);
        }
    }

    private function getResultDecoded(string $endpoint): array
    {
        return json_decode($this->getImage($endpoint), true);
    }

    private function getImage(string $endpoint): string
    {
        $res = $this->client->request('GET', $this->getApiUrl($endpoint));

        $contents = $res->getBody()->getContents();
        if ($res->getStatusCode() >= 400) {
            throw ApiException::create($res->getStatusCode(), $contents);
        }

        return $contents;
    }

    public function get(string $fileName): string
    {
        try {
            Assertion::notEmpty($fileName);

            return $this->getImage('/image/' . $fileName);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ApiException::from($e);
        }
    }

    public function useNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }
}
