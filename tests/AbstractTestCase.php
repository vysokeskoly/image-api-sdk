<?php

namespace VysokeSkoly\Tests\ImageApi\Sdk;

use Mockery as m;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @var bool */
    protected static $isGmagickEnabled = true;

    protected function checkGmagick()
    {
        if (!class_exists(\Gmagick::class)) {
            self::$isGmagickEnabled = false;
            $this->markAsRisky();
            // @codingStandardsIgnoreStart
            require_once __DIR__ . '/Fixtures/Gmagick.php';
            // @codingStandardsIgnoreEnd
        }
    }
}
