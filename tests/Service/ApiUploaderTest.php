<?php

namespace VysokeSkoly\Tests\ImageApi\Sdk\Service;

use Guzzle\Stream\StreamInterface;
use GuzzleHttp\Client;
use Mockery as m;
use Psr\Http\Message\ResponseInterface;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Exception\UploadException;
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

        $response = $this->mockResponse(200);
        $this->mockClientRequest($apiUrl, $fileName, $content, $response);

        $this->apiUploader->saveString($content, $fileName);

        $this->assertTrue(true);
    }

    /**
     * @return ResponseInterface|m\MockInterface
     */
    private function mockResponse(int $statusCode, string $contents = ''): ResponseInterface
    {
        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn($statusCode);

        if (!empty($contents)) {
            $body = m::mock(StreamInterface::class);
            $body->shouldReceive('getContents')->andReturn($contents);

            $response->shouldReceive('getBody')->andReturn($body);
        }

        return $response;
    }

    private function mockClientRequest(
        string $apiUrl,
        string $fileName,
        string $content,
        ResponseInterface $response
    ): void {
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
    }

    /**
     * @dataProvider emptyProvider
     */
    public function testShouldThrowImageException(string $content, string $fileName)
    {
        $this->expectException(ImageException::class);

        $this->apiUploader->saveString($content, $fileName);
    }

    public function emptyProvider()
    {
        return [
            // content, fileName
            'emptyContent' => ['', 'fileName'],
            'emptyFileName' => ['content', ''],
            'emptyBoth' => ['', ''],
        ];
    }

    /**
     * @dataProvider errorStatusCodeProvider
     */
    public function testShouldThrowInvalidUploadException(int $errorStatusCode)
    {
        $errorContents = 'errorContents';
        $this->expectException(UploadException::class);
        $this->expectExceptionMessage($errorContents);

        $content = 'content';
        $fileName = 'filename';
        $apiUrl = self::API_URL . '/image?apikey=' . self::API_KEY;

        $response = $this->mockResponse($errorStatusCode, $errorContents);
        $this->mockClientRequest($apiUrl, $fileName, $content, $response);

        $this->apiUploader->saveString($content, $fileName);
    }

    public function errorStatusCodeProvider()
    {
        return [
            '400' => [400],
            '401' => [401],
            '404' => [404],
            '500' => [500],
            '502' => [502],
            '503' => [503],
        ];
    }
}
