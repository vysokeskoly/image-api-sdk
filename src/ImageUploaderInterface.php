<?php

namespace VysokeSkoly\ImageApi\Sdk;

use VysokeSkoly\ImageApi\Sdk\Entity\Result;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;

interface ImageUploaderInterface
{
    /**
     * @param string $uploadedFile Full path file name
     * @param int $minWidth
     * @param int $minHeight
     * @param float|null $aspectRatio
     * @return Result
     *
     * @throws ImageException
     */
    public function validateAndUpload(
        string $uploadedFile,
        int $minWidth,
        int $minHeight,
        float $aspectRatio = null
    ): Result;

    /**
     * @param string $uploadedFile Full path file name
     * @return Result
     *
     * @throws ImageException
     */
    public function upload(string $uploadedFile): Result;
}
