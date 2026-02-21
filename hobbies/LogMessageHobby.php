<?php

declare(strict_types=1);

namespace hobbies;

use src\Attributes\MaxAttempts;
use src\Attributes\OnQueue;
use src\Contracts\Hobby;

#[OnQueue('default')]
#[MaxAttempts(3)]
readonly class LogMessageHobby implements Hobby
{
    public function __construct(
        private string $message,
    ) {}

    public function handle(): void
    {
        $line = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $this->message);

        file_put_contents(__DIR__ . '/../storage/logs.txt', $line, FILE_APPEND);
    }
}
