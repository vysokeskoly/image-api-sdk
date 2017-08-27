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
            $this->apiUrl . $endpoint . $this->getAuth(),
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

    private function getAuth(): string
    {
        return '?apikey=' . $this->apiKey;
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
        $res = $this->client->request(
            'DELETE',
            $this->apiUrl . $endpoint . $this->getAuth()
        );

        if ($res->getStatusCode() >= 400) {
            throw ApiException::create($res->getStatusCode(), $res->getBody()->getContents());
        }
    }
}