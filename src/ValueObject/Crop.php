<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

class Crop
{
    public function __construct(private readonly Point $start, private readonly ImageSize $size)
    {
    }

    public static function parse(array $crop): ?self
    {
        if (empty($crop['x']) || empty($crop['y']) || empty($crop['x2']) || empty($crop['y2'])) {
            return null;
        }

        $start = new Point((int) $crop['x'], (int) $crop['y']);
        $size = new ImageSize(
            ((int) $crop['x2']) - $start->getX(),
            ((int) $crop['y2']) - $start->getY(),
        );

        return new self($start, $size);
    }

    public function getStart(): Point
    {
        return $this->start;
    }

    public function getSize(): ImageSize
    {
        return $this->size;
    }
}
