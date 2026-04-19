<?php

declare(strict_types=1);

namespace demos;

use src\Contracts\AwaitResolver;

final class DemoAwaitResolver implements AwaitResolver
{
    public function resolve(array $signal): mixed
    {
        return [
            'operation' => (string) ($signal['operation'] ?? 'unknown'),
            'completed_at' => date('Y-m-d H:i:s'),
            'context' => is_array($signal['context'] ?? null) ? $signal['context'] : [],
            'data' => is_array($signal['context']['result'] ?? null)
                ? $signal['context']['result']
                : ['ok' => true],
        ];
    }
}
