<?php

namespace VysokeSkoly\ImageApi\Sdk;

use VysokeSkoly\ImageApi\Sdk\Entity\Result;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;

interface ImageUploaderInterface
{
    /**
     * @param string $imagePath Full path file name
     * @param int $minWidth
     * @param int $minHeight
     * @param float|null $aspectRatio
     * @return Result
     *
     * @throws ImageException
     */
    public function validateAndUpload(
        string $imagePath,
        int $minWidth,
        int $minHeight,
        float $aspectRatio = null
    ): Result;

    /**
     * @param string $imagePath Full path file name
     * @return Result
     *
     * @throws ImageException
     */
    public function upload(string $imagePath): Result;

    /**
     * @param string $imageName
     *
     * @throws ImageException
     */
    public function delete(string $imageName): void;

    /**
     * @return array
     *
     * @throws ImageException
     */
    public function listAllImageNames(): array;

    /**
     * @param string $fileName
     * @return string
     *
     * @throws ImageException
     */
    public function get(string $fileName): string;
}
