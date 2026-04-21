<?php

declare(strict_types=1);

namespace src\Contracts;

interface AwaitOperationHandler
{
    public function supports(string $operation): bool;

    public function resolve(array $signal): array;
}
