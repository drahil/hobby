<?php

declare(strict_types=1);

namespace src;

use Fiber;
use src\Contracts\Awaitable;

final class Async
{
    public static function await(Awaitable $awaitable, ?int $timeoutSeconds = null): mixed
    {
        $now = microtime(true);

        return Fiber::suspend([
            'awaitable' => $awaitable,
            'timeoutAt' => $timeoutSeconds === null ? null : $now + max(0, $timeoutSeconds),
        ]);
    }

    public static function suspendFor(float $seconds): void
    {
        self::await(new TimerAwaitable($seconds));
    }
}
