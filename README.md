VysokeSkoly / ImageApiSdk
=========================

[![Build Status](https://travis-ci.org/vysokeskoly/image-api-sdk.svg?branch=master)](https://travis-ci.org/vysokeskoly/image-api-sdk)

Sdk for ImageApi

## Installation
```json
{
    "vysokeskoly/image-api-sdk": "dev-master"
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
