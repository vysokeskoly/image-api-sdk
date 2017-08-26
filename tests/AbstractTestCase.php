<?php

namespace VysokeSkoly\Tests\ImageApi\Sdk;

use Mockery as m;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}
