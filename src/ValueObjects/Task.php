<?php

declare(strict_types=1);

namespace src\ValueObjects;

use Fiber;
use src\Contracts\Hobby;

final readonly class Task
{
    public function __construct(
        public Hobby $hobby,
        public Fiber $fiber,
        public bool $started,
        public float $runAt,
        public mixed $signal,
        public mixed $context = null,
    ) {
    } 
}
