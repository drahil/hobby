<?php

declare(strict_types=1);

namespace demos\normal;

use src\Attributes\MaxAttempts;
use src\Attributes\OnQueue;
use src\Contracts\Hobby;

#[OnQueue('normal-demo')]
#[MaxAttempts(3)]
readonly class FailingHobby implements Hobby
{
    public function handle(): void
    {
        throw new \RuntimeException('This hobby always fails.');
    }
}
