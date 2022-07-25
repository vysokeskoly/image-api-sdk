<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Service;

use VysokeSkoly\ImageApi\Sdk\AbstractTestCase;
use VysokeSkoly\ImageApi\Sdk\ValueObject\Api;

class ApiProviderTest extends AbstractTestCase
{
    public function testShouldProvideGivenApiWithDefaultNamespace(): void
    {
        $apiProvider = new ApiProvider('http://api', '12345');
        $api = $apiProvider->getImageApi();

        $this->assertEquals(new Api('http://api', '12345', null), $api);
    }

    public function testShouldProvideGivenApiWithNamespace(): void
    {
        $apiProvider = new ApiProvider('http://api', '12345', 'namespace');
        $api = $apiProvider->getImageApi();

        $this->assertEquals(new Api('http://api', '12345', 'namespace'), $api);
    }
}
