<?php

declare(strict_types=1);

namespace src\Contracts;

interface AwaitResolver
{
    public function resolve(array $signal): mixed;
}
