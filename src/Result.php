<?php

namespace VysokeSkoly\ImageApi\Sdk;

class Result
{
    /** @var string */
    private $url;

    /** @var string */
    private $hash;

    /** @var int */
    private $width;

    /** @var int */
    private $height;

    /** @var Coordination|null */
    private $coordination = null;

    public function __construct(string $url, string $hash, int $width, int $height)
    {
        $this->url = $url;
        $this->hash = $hash;
        $this->width = $width;
        $this->height = $height;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setCoordination(Coordination $coordination): self
    {
        $this->coordination = $coordination;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(
            [
                'url' => $this->getUrl(),
                'hash' => $this->getHash(),
                'width' => $this->getWidth(),
                'height' => $this->getHash(),
            ],
            $this->coordination ? $this->coordination->toArray() : []
        );
    }
}
