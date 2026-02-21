<?php

declare(strict_types=1);

namespace hobbies;

use src\Attributes\MaxAttempts;
use src\Attributes\OnQueue;
use src\Contracts\Hobby;

#[OnQueue('default')]
#[MaxAttempts(3)]
readonly class FailingHobby implements Hobby
{
    public function __construct() {}

    public function handle(): void
    {
        throw new \RuntimeException("This hobby always fails.");
    }
}
