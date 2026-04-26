<?php

declare(strict_types=1);

namespace src\Contracts;

interface Awaitable
{
    public function ready(): bool;

    public function result(): mixed;

    public function nextCheckAt(): float;
}
