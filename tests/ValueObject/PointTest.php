<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\ValueObject;

use PHPUnit\Framework\TestCase;

class PointTest extends TestCase
{
    public function testShouldMovePoint(): void
    {
        $point = new Point(0, 0);
        $moved = $point->move(10);

        $this->assertNotSame($point, $moved);
        $this->assertSame(10, $moved->getX());
        $this->assertSame(10, $moved->getY());
    }

    public function testShouldBeInBoxThenMoveOut(): void
    {
        $point = new Point(0, 0);
        $size = new ImageSize(10, 10);

        $isInBox = $point->in($size->asBox());
        $this->assertTrue($isInBox);

        $isInBox = $point
            ->move(11)
            ->in($size->asBox());
        $this->assertFalse($isInBox);
    }
}
