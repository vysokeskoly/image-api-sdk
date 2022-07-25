<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk;

use Assert\Assertion as BaseAssertion;
use VysokeSkoly\ImageApi\Sdk\Exception\ImageException;

/** @internal */
class Assertion extends BaseAssertion
{
    protected static $exceptionClass = ImageException::class;
}
