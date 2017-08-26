<?php

namespace VysokeSkoly\Tests\ImageApi\Sdk\Service;

use GuzzleHttp\Client;
use Mockery as m;
use Psr\Http\Message\ResponseInterface;
use VysokeSkoly\ImageApi\Sdk\Service\ApiUploader;
use VysokeSkoly\Tests\ImageApi\Sdk\AbstractTestCase;

class ApiUploaderTest extends AbstractTestCase
{
    const API_URL = 'api';
    const API_KEY = 'key';

    /** @var ApiUploader */
    private $apiUploader;

    /** @var Client|m\MockInterface */
    private $client;

    public function setUp()
    {
        $this->client = m::mock(Client::class);

        $this->apiUploader = new ApiUploader(
            $this->client,
            self::API_URL,
            self::API_KEY
        );
    }

    public function testShouldSaveImage()
    {
        $content = 'content';
        $fileName = 'filename';
        $apiUrl = self::API_URL . '/image?apikey=' . self::API_KEY;

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200);

        $this->client->shouldReceive('request')
            ->with(
                'POST',
                $apiUrl,
                [
                    'multipart' => [
                        [
                            'name' => $fileName,
                            'contents' => $content,
                            'filename' => $fileName,
                        ],
                    ],
                ]
            )
            ->once()
            ->andReturn($response);

        $this->apiUploader->saveString($content, $fileName);

        $this->assertTrue(true);
    }
}
