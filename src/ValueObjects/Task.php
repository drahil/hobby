<?php

declare(strict_types=1);

namespace src\ValueObjects;

use Fiber;

final readonly class Task
{
    public function __construct(
        public Fiber $fiber,
        public float $runAt,
        public mixed $waitingOn,
        public mixed $context = null,
    ) {
    } 
}
