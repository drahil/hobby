<?php

declare(strict_types=1);

namespace src\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Delay
{
    public function __construct(
        public int $seconds = 0,
    ) {}
}