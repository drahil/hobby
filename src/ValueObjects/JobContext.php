<?php

declare(strict_types=1);

namespace src\ValueObjects;

final readonly class JobContext
{
    /**
     * @param list<mixed> $args
     */
    public function __construct(
        public string $class,
        public array $args,
        public int $attempts,
        public int $maxAttempts,
        public string $queue,
    ) {}
}
