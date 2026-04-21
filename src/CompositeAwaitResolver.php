<?php

declare(strict_types=1);

namespace src;

use src\Contracts\AwaitResolver;

final readonly class CompositeAwaitResolver implements AwaitResolver
{
    public function __construct(
        private array $handlers,
    ) {}

    public function resolve(array $signal): mixed
    {
        $operation = (string) ($signal['operation'] ?? '');

        foreach ($this->handlers as $handler) {
            if ($handler->supports($operation)) {
                return $handler->resolve($signal);
            }
        }

        throw new \LogicException("No await handler registered for operation [{$operation}].");
    }
}
