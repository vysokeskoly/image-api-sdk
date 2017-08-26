<?php

namespace VysokeSkoly\ImageApi\Sdk\Service;

use Assert\Assertion;
use Assert\InvalidArgumentException;
use GuzzleHttp\Client;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\UploadException;

class ApiUploader
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

            $this->postImage('/image', $content, $fileName);
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
            throw UploadException::create($res->getStatusCode(), $res->getBody()->getContents());
        }
    }

    private function getAuth(): string
    {
        return '?apikey=' . $this->apiKey;
    }
}
