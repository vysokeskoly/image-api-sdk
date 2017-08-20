<?php

namespace VysokeSkoly\ImageApi\Sdk;

class Coordination
{
    /** @var int */
    private $cropTopLeftX;

    /** @var int */
    private $cropTopLeftY;

    /** @var int */
    private $cropBottomRightX;

    /** @var int */
    private $cropBottomRightY;

    public function __construct(int $cropTopLeftX, int $cropTopLeftY, int $cropBottomRightX, int $cropBottomRightY)
    {
        $this->cropTopLeftX = $cropTopLeftX;
        $this->cropTopLeftY = $cropTopLeftY;
        $this->cropBottomRightX = $cropBottomRightX;
        $this->cropBottomRightY = $cropBottomRightY;
    }

    public function getCropTopLeftX(): int
    {
        return $this->cropTopLeftX;
    }

    public function getCropTopLeftY(): int
    {
        return $this->cropTopLeftY;
    }

    public function getCropBottomRightX(): int
    {
        return $this->cropBottomRightX;
    }

    public function getCropBottomRightY(): int
    {
        return $this->cropBottomRightY;
    }

    public function toArray(): array
    {
        return [
            'crop_topleft_x' => $this->getCropTopLeftX(),
            'crop_topleft_y' => $this->getCropTopLeftY(),
            'crop_bottomright_x' => $this->getCropBottomRightX(),
            'crop_bottomright_y' => $this->getCropBottomRightY(),
        ];
    }
}
