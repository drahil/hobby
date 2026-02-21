<?php

declare(strict_types=1);

namespace src\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class MaxAttempts
{
    public function __construct(
        public int $tries = 3,
    ) {}
}