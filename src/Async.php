<?php

declare(strict_types=1);

namespace src;

use Fiber;

final class Async
{
    public static function await(string $operation, array $context = []): array
    {
        return Fiber::suspend([
            'type' => 'await',
            'operation' => $operation,
            'context' => $context,
            'until' => microtime(true) + max(0, (float) ($context['delay'] ?? 0)),
        ]);
    }
}
