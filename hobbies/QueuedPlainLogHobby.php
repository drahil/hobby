<?php

declare(strict_types=1);

namespace hobbies;

use src\Attributes\OnQueue;
use src\Contracts\Hobby;

#[OnQueue('fiber-worker-demo')]
readonly class QueuedPlainLogHobby implements Hobby
{
    public function __construct(
        private string $name,
    ) {}

    public function handle(): void
    {
        echo sprintf("[%s] %s: plain hobby start\n", date('H:i:s'), $this->name);
        usleep(200_000);
        echo sprintf("[%s] %s: plain hobby done\n", date('H:i:s'), $this->name);
    }
}
