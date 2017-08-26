<?php

namespace VysokeSkoly\Tests\ImageApi\Sdk\Fixtures;

use VysokeSkoly\ImageApi\Sdk\ImageApiUploader;
use VysokeSkoly\ImageApi\Sdk\Service\ApiUploader;
use VysokeSkoly\ImageApi\Sdk\Service\ImageFactory;
use VysokeSkoly\ImageApi\Sdk\Service\ImageValidator;

class TestableImageApiUploader extends ImageApiUploader
{
    public function setImageValidator(ImageValidator $imageValidator)
    {
        $this->imageValidator = $imageValidator;
    }

    public function setApiUploader(ApiUploader $apiUploader)
    {
        $this->apiUploader = $apiUploader;
    }

    public function setImageFactory(ImageFactory $imageFactory)
    {
        $this->imageFactory = $imageFactory;
    }
}
