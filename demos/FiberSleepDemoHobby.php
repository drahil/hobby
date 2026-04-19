<?php

declare(strict_types=1);

namespace demos;

use src\Async;
use src\Attributes\ExecuteConcurrently;
use src\Contracts\Hobby;

#[ExecuteConcurrently]
readonly class FiberSleepDemoHobby implements Hobby
{
    public function __construct(
        private string $name,
        private int|float $delaySeconds,
        private int $steps = 3,
    ) {}

    public function handle(): void
    {
        for ($step = 1; $step <= $this->steps; $step++) {
            printf("[%s] %s step %d\n", date('H:i:s'), $this->name, $step);
            Async::sleep($this->delaySeconds);
        }

        printf("[%s] %s done\n", date('H:i:s'), $this->name);
    }
}
