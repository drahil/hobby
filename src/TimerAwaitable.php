<?php

declare(strict_types=1);

namespace src;

use src\Contracts\Awaitable;

final readonly class TimerAwaitable implements Awaitable
{
    private float $readyAt;

    public function __construct(float $seconds)
    {
        $this->readyAt = microtime(true) + max(0.0, $seconds);
    }

    public function ready(): bool
    {
        return microtime(true) >= $this->readyAt;
    }

    public function result(): null
    {
        return null;
    }

    public function nextCheckAt(): float
    {
        return $this->readyAt;
    }
}
