<?php

namespace VysokeSkoly\Tests\ImageApi\Sdk\Service;

use Guzzle\Stream\StreamInterface;
use GuzzleHttp\Client;
use Mockery as m;
use Psr\Http\Message\ResponseInterface;
use VysokeSkoly\ImageApi\Sdk\Exception\ApiException;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;
use VysokeSkoly\ImageApi\Sdk\Service\ApiService;
use VysokeSkoly\Tests\ImageApi\Sdk\AbstractTestCase;

class ApiServiceTest extends AbstractTestCase
{
    const API_URL = 'api';
    const API_KEY = 'key';
    const NAMESPACE = 'my-namespace';

    /** @var ApiService */
    private $apiService;

    /** @var Client|m\MockInterface */
    private $client;

    public function setUp()
    {
        $this->client = m::mock(Client::class);

        $this->apiService = new ApiService(
            $this->client,
            self::API_URL,
            self::API_KEY
        );
    }

    /**
     * @dataProvider provideNamespace
     */
    public function testShouldSaveImage(?string $namespace)
    {
        $content = 'content';
        $fileName = 'filename';

        $baseApiUrl = self::API_URL . '/image/?apikey=' . self::API_KEY;
        $apiUrl = $namespace !== null
            ? $baseApiUrl . '&namespace=' . $namespace
            : $baseApiUrl;

        $response = $this->mockResponse(200);
        $this->mockClientPostRequest($apiUrl, $fileName, $content, $response);

        if ($namespace !== null) {
            $this->apiService->useNamespace($namespace);
        }

        $this->apiService->saveString($content, $fileName);
    }

    public function provideNamespace(): array
    {
        return [
            // namespace
            'without' => [null],
            'with namespace' => [self::NAMESPACE],
        ];
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

    private function mockClientPostRequest(
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

        $this->apiService->saveString($content, $fileName);
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
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage($errorContents);

        $content = 'content';
        $fileName = 'filename';
        $apiUrl = self::API_URL . '/image/?apikey=' . self::API_KEY;

        $response = $this->mockResponse($errorStatusCode, $errorContents);
        $this->mockClientPostRequest($apiUrl, $fileName, $content, $response);

        $this->apiService->saveString($content, $fileName);
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

    /** @dataProvider provideNamespace */
    public function testShouldDeleteImage(?string $namespace)
    {
        $fileName = 'file-to-delete';

        $response = $this->mockResponse(200);
        $this->mockClientDeleteRequest($namespace, $fileName, $response);

        if ($namespace !== null) {
            $this->apiService->useNamespace($namespace);
        }

        $this->apiService->delete($fileName);
    }

    private function mockClientDeleteRequest(?string $namespace, string $fileName, ResponseInterface $response)
    {
        $baseApiUrl = self::API_URL . '/image/' . $fileName . '?apikey=' . self::API_KEY;
        $apiUrl = $namespace !== null
            ? $baseApiUrl . '&namespace=' . $namespace
            : $baseApiUrl;

        $this->client->shouldReceive('request')
            ->with('DELETE', $apiUrl)
            ->once()
            ->andReturn($response);
    }

    public function testShouldThrowImageExceptionOnEmptyDeleteFile()
    {
        $this->expectException(ImageException::class);

        $this->apiService->delete('');
    }

    /**
     * @dataProvider errorStatusCodeProvider
     */
    public function testShouldThrowApiExceptionOnDeleteImage(int $errorStatusCode)
    {
        $errorContents = 'errorContents';
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage($errorContents);

        $fileName = 'file-to-delete';

        $response = $this->mockResponse($errorStatusCode, $errorContents);
        $this->mockClientDeleteRequest(null, $fileName, $response);

        $this->apiService->delete($fileName);
    }

    /** @dataProvider provideNamespace */
    public function testShouldListAll(?string $namespace)
    {
        $entryPoint = '/list/';
        $expectedList = ['file'];
        $response = $this->mockResponse(200, json_encode($expectedList));
        $this->mockClientGetRequest($namespace, $entryPoint, $response);

        if ($namespace !== null) {
            $this->apiService->useNamespace($namespace);
        }

        $result = $this->apiService->listAll();

        $this->assertSame($expectedList, $result);
    }

    private function mockClientGetRequest(?string $namespace, string $expectedEntryPoint, ResponseInterface $response)
    {
        $baseApiUrl = self::API_URL . $expectedEntryPoint . '?apikey=' . self::API_KEY;
        $apiUrl = $namespace !== null
            ? $baseApiUrl . '&namespace=' . $namespace
            : $baseApiUrl;

        $this->client->shouldReceive('request')
            ->with('GET', $apiUrl)
            ->once()
            ->andReturn($response);
    }

    /**
     * @dataProvider errorStatusCodeProvider
     */
    public function testShouldThrowApiExceptionOnListAll(int $errorStatusCode)
    {
        $entryPoint = '/list/';

        $errorContents = 'errorContents';
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage($errorContents);

        $response = $this->mockResponse($errorStatusCode, $errorContents);
        $this->mockClientGetRequest(null, $entryPoint, $response);

        $this->apiService->listAll();
    }

    /** @dataProvider provideNamespace */
    public function testShouldGetImage(?string $namespace)
    {
        $fileName = 'fileName';
        $entryPoint = '/image/' . $fileName;
        $expectedContent = 'content';
        $response = $this->mockResponse(200, $expectedContent);
        $this->mockClientGetRequest($namespace, $entryPoint, $response);

        if ($namespace !== null) {
            $this->apiService->useNamespace($namespace);
        }

        $result = $this->apiService->get($fileName);

        $this->assertSame($expectedContent, $result);
    }

    /**
     * @dataProvider errorStatusCodeProvider
     */
    public function testShouldThrowApiExceptionOnGetImage(int $errorStatusCode)
    {
        $fileName = 'fileName';
        $entryPoint = '/image/' . $fileName;

        $errorContents = 'errorContents';
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage($errorContents);

        $response = $this->mockResponse($errorStatusCode, $errorContents);
        $this->mockClientGetRequest(null, $entryPoint, $response);

        $this->apiService->get($fileName);
    }

    public function testShouldThrowImageExceptionOnEmptyGetImage()
    {
        $this->expectException(ImageException::class);

        $this->apiService->get('');
    }
}
