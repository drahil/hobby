<?php

declare(strict_types=1);

namespace src;

use Fiber;

final class Async
{
    public static function sleep(int|float $seconds): void
    {
        Fiber::suspend([
            'type' => 'sleep',
            'until' => microtime(true) + max(0, (float) $seconds),
        ]);
    }

    public static function await(string $operation, array $context = []): array
    {
        return Fiber::suspend([
            'type' => 'await',
            'operation' => $operation,
            'context' => $context,
            'until' => microtime(true) + max(0, (float) ($context['delay'] ?? 0)),
        ]);
    }

    public static function defer(string $operation, array $context = []): void
    {
        throw new \LogicException('Async::defer() is not implemented yet.');
    }
}
