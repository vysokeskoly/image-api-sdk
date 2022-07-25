VysokeSkoly / ImageApiSdk
=========================

[![Latest Stable Version](https://img.shields.io/packagist/v/vysokeskoly/image-api-sdk.svg)](https://packagist.org/packages/vysokeskoly/image-api-sdk)
[![License](https://img.shields.io/packagist/l/vysokeskoly/image-api-sdk.svg)](https://packagist.org/packages/vysokeskoly/image-api-sdk)
[![Checks](https://github.com/vysokeskoly/image-api-sdk/actions/workflows/checks.yaml/badge.svg)](https://github.com/vysokeskoly/image-api-sdk/actions/workflows/checks.yaml)
[![Build](https://github.com/vysokeskoly/image-api-sdk/actions/workflows/php-checks.yaml/badge.svg)](https://github.com/vysokeskoly/image-api-sdk/actions/workflows/php-checks.yaml)
[![Coverage Status](https://coveralls.io/repos/github/vysokeskoly/image-api-sdk/badge.svg)](https://coveralls.io/github/vysokeskoly/image-api-sdk)

> Sdk for [ImageApi](https://github.com/vysokeskoly/image-api)

## Installation
```json
{
    "vysokeskoly/image-api-sdk": "^1.0"
}
```

## Requirements
- `PHP 7.4`
- Corresponding version of [ImageApi](https://github.com/vysokeskoly/image-api)

## Usage

### In Symfony application
```yaml
services:
    _defaults:
        autowire: true      # Automatically injects dependencies in your services.
        autoconfigure: true # Automatically registers your services as commands, event subscribers, etc.

    VysokeSkoly\ImageApi\Sdk\ImageUploaderInterface: '@VysokeSkoly\ImageApi\Sdk\ImageApiUploader'

    VysokeSkoly\ImageApi\Sdk\Service\ApiProvider:
        $apiUrl: '%image_api_url%'
        $apiKey: '%image_api_key%'
        $namespace: '%image_api_namespace%'

    VysokeSkoly\ImageApi\Sdk\Service\CommandQueryFactory: ~

    VysokeSkoly\ImageApi\Sdk\ImageApiUploader:
        arguments:
            $allowedMimeTypes:
                GIF: 'image/gif'
                JPEG: 'image/jpeg'
                PNG: 'image/png'
            $imageMaxFileSize: 8536064 # 8 * 1024 * 124 = 8 MB
            $imageMaxSize: 2048
        calls:
            - [ enableCache ]

    # optional
    VysokeSkoly\ImageApi\Sdk\Service\SavedImageDecoder:
        arguments:
            $imageBaseUrl: '%image_api_url%'
        tags:
            - { name: lmc_cqrs.response_decoder, priority: 55 }
```

// todo - zamyslet se nad configuraci pro symfony (napr jak predat Api atd - spis by bylo lepsi tady zaregistrovat nejaky manager tech scalarnich typu a ten uploader by si ho vzal..)
    - nebo pridat factory (factory metodu ? - kouknout jak se to v tech services.yaml ted dela)

NOTE: If you need size information about just Saved images, you need to enable Image Cache for a decoder.
```php
\VysokeSkoly\ImageApi\Sdk\Service\ImagesCache::enable();
```

or in services declaration
```yaml
    VysokeSkoly\ImageApi\Sdk\ImageApiUploader:
        ...
        calls:
            - [ enableCache ]
```
