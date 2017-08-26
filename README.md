VysokeSkoly / ImageApiSdk
=========================

[![Build Status](https://travis-ci.org/vysokeskoly/image-api-sdk.svg?branch=master)](https://travis-ci.org/vysokeskoly/image-api-sdk)
[![Coverage Status](https://coveralls.io/repos/github/vysokeskoly/image-api-sdk/badge.svg?branch=master)](https://coveralls.io/github/vysokeskoly/image-api-sdk?branch=master)

Sdk for ImageApi

## Installation
```json
{
    "vysokeskoly/image-api-sdk": "^1.0"
}
```

## Requirements
- `PHP 7.1`

## Usage

### In Symfony application
```yaml
services:
    VysokeSkoly\ImageApi\Sdk\ImageApiUploader:
        arguments:
            $allowedMimeTypes:
                GIF: 'image/gif'
                JPEG: 'image/jpeg'
                PNG: 'image/png'
            $imageMaxFileSize: 2097152  # 2 * 1024 * 1024 - 2MB
            $imageMaxSize: 2048
            $imageUrl: '%imageUrl%'
            $apiUrl: '%apiUrl%'
            $apiKey: '%apiKey%'
```
