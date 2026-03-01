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
        $path = __DIR__ . '/../storage/logs.txt';

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        $line = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $this->message);

        if (file_put_contents($path, $line, FILE_APPEND) === false) {
            throw new \RuntimeException("Failed to write to {$path}");
        }
    }
}
