<?php

namespace VysokeSkoly\ImageApi\Sdk;

class ImageFactory
{
    public function createImage(string $imageData): \Gmagick
    {
        $image = new \Gmagick();
        $image->readImageBlob($imageData);

        if (method_exists($image, 'setCompressionQuality')) {
            $image->setCompressionQuality(100);
        }

        if ($image->getImageFormat() === 'GIF') {
            $image->setImageFormat('PNG');
        }

        return $image;
    }
}
