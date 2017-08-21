<?php

namespace VysokeSkoly\ImageApi\Sdk;

use GuzzleHttp\Client;

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

    public function saveString($content, string $fileName): void
    {
        $this->postImage('/image', $content, $fileName);
    }

    private function postImage(string $endpoint, $content, string $fileName): void
    {
        $res = $this->client->request(
            'POST',
            $this->apiUrl . $endpoint . $this->getAuth(),
            [
                'multipart' => [
                    [
                        'name' => 'FileContents',
                        'contents' => $content,
                        'filename' => $fileName,
                    ],
                ],
            ]
        );

        if ($res->getStatusCode() >= 400) {
            throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
        }
    }

    private function getAuth(): string
    {
        return '?apikey=' . $this->apiKey;
    }
}
