<?php

declare(strict_types=1);

namespace hobbies;

use src\Async;
use src\Attributes\ExecuteConcurrently;
use src\Attributes\OnQueue;
use src\Contracts\Hobby;

#[OnQueue('fiber-worker-demo')]
#[ExecuteConcurrently]
readonly class QueuedFiberAwaitHobby implements Hobby
{
    public function __construct(
        private string $name,
    ) {}

    public function handle(): void
    {
        $this->log("{$this->name}: requesting company profile");

        $profile = Async::await('company.profile', [
            'delay' => 0.75,
            'result' => [
                'company' => $this->name,
                'industry' => 'software',
            ],
        ]);

        $this->log("{$this->name}: received profile " . json_encode($profile['data'], JSON_THROW_ON_ERROR));

        $risk = Async::await('company.risk-score', [
            'delay' => 1.25,
            'result' => [
                'company' => $this->name,
                'score' => 17,
            ],
        ]);

        $this->log("{$this->name}: received risk score " . json_encode($risk['data'], JSON_THROW_ON_ERROR));
    }

    private function log(string $message): void
    {
        echo sprintf("[%s] %s\n", date('H:i:s'), $message);
    }
}
