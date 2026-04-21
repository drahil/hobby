<?php

declare(strict_types=1);

namespace demos;

use src\Contracts\AwaitOperationHandler;

final class CompanyProfileAwaitHandler implements AwaitOperationHandler
{
    public function supports(string $operation): bool
    {
        return $operation === 'company.profile';
    }

    public function resolve(array $signal): array
    {
        return [
            'operation' => 'company.profile',
            'completed_at' => date('Y-m-d H:i:s'),
            'context' => is_array($signal['context'] ?? null) ? $signal['context'] : [],
            'data' => is_array($signal['context']['result'] ?? null)
                ? $signal['context']['result']
                : ['ok' => true],
        ];
    }
}
