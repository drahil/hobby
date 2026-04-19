<?php

declare(strict_types=1);

namespace src\ValueObjects;

final readonly class AdvanceResult
{
    private function __construct(
        public string $status,
        public ?Task $task = null,
        public ?\Throwable $error = null,
    ) {}

    public static function idle(): self
    {
        return new self('idle');
    }

    public static function progressed(): self
    {
        return new self('progressed');
    }

    public static function completed(Task $task): self
    {
        return new self('completed', $task);
    }

    public static function failed(Task $task, \Throwable $error): self
    {
        return new self('failed', $task, $error);
    }

    public function isIdle(): bool
    {
        return $this->status === 'idle';
    }

    public function isProgressed(): bool
    {
        return $this->status === 'progressed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
