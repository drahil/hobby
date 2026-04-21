<?php

declare(strict_types=1);

namespace demos;

use src\Contracts\AwaitOperationHandler;

final class CompanyRiskScoreAwaitHandler implements AwaitOperationHandler
{
    public function supports(string $operation): bool
    {
        return $operation === 'company.risk-score';
    }

    public function resolve(array $signal): array
    {
        return [
            'operation' => 'company.risk-score',
            'completed_at' => date('Y-m-d H:i:s'),
            'context' => is_array($signal['context'] ?? null) ? $signal['context'] : [],
            'data' => is_array($signal['context']['result'] ?? null)
                ? $signal['context']['result']
                : ['ok' => true],
        ];
    }
}
