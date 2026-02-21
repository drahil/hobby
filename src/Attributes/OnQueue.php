<?php

declare(strict_types=1);

namespace src\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class OnQueue
{
    public function __construct(
        public string $name = 'default',
    ) {}
}
