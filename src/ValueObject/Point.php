<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use Imagine\Image\BoxInterface;
use Imagine\Image\PointInterface;

class Point implements PointInterface
{
    public function __construct(private readonly int $x, private readonly int $y)
    {
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function in(BoxInterface $box): bool
    {
        return $this->x < $box->getWidth() && $this->y < $box->getHeight();
    }

    public function move($amount): self
    {
        return new self($this->x + $amount, $this->y + $amount);
    }

    public function __toString(): string
    {
        return sprintf('(%d, %d)', $this->x, $this->y);
    }
}
