<?php

declare(strict_types=1);

namespace demos\concurrent;

use src\Contracts\Awaitable;

final readonly class FakeCompanyApiRequest implements Awaitable
{
    private float $readyAt;

    public function __construct(
        private string $operation,
        private string $company,
    ) {
        $this->readyAt = microtime(true) + match ($this->operation) {
            'profile' => 0.75,
            'risk-score' => 1.25,
            default => 1.0,
        };
    }

    public function ready(): bool
    {
        return microtime(true) >= $this->readyAt;
    }

    public function result(): array
    {
        return match ($this->operation) {
            'profile' => [
                'company' => $this->company,
                'industry' => 'software',
            ],
            'risk-score' => [
                'company' => $this->company,
                'score' => 17,
            ],
            default => ['ok' => true],
        };
    }

    public function nextCheckAt(): float
    {
        return $this->readyAt;
    }
}
