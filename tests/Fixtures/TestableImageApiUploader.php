<?php

namespace VysokeSkoly\ImageApi\Sdk\Fixtures;

use VysokeSkoly\ImageApi\Sdk\ImageApiUploader;
use VysokeSkoly\ImageApi\Sdk\Service\ApiService;
use VysokeSkoly\ImageApi\Sdk\Service\ImageFactory;
use VysokeSkoly\ImageApi\Sdk\Service\ImageValidator;

class TestableImageApiUploader extends ImageApiUploader
{
    public function setImageValidator(ImageValidator $imageValidator)
    {
        $this->imageValidator = $imageValidator;
    }

    public function setApiService(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function setImageFactory(ImageFactory $imageFactory)
    {
        $this->imageFactory = $imageFactory;
    }
}
