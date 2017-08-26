<?php

// @codingStandardsIgnoreStart
class Gmagick
// @codingStandardsIgnoreEnd
{
    public static $imageFormat = 'JPG';

    /** @var int */
    private $width = 0;

    /** @var int */
    private $height = 0;

    /** @var string */
    private $content = '';

    public function setWidth(int $width)
    {
        $this->width = $width;
    }

    public function setHeight(int $height)
    {
        $this->height = $height;
    }

    public function getimageheight()
    {
        return $this->height;
    }

    public function getimagewidth()
    {
        return $this->width;
    }

    public function setCompressionQuality($quality)
    {
    }

    public function setImageFormat($imageFormat)
    {
        self::$imageFormat = $imageFormat;
    }

    public function getImageFormat()
    {
        return self::$imageFormat;
    }

    public function readimageblob($imageContents, $filename = null)
    {
        $this->content = $imageContents;
    }

    public function scaleimage($width, $height, $fit = false)
    {
    }

    public function __toString()
    {
        return $this->content;
    }
}
