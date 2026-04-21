<?php

declare(strict_types=1);

namespace hobbies;

use src\Async;
use src\Attributes\ExecuteConcurrently;
use src\Contracts\Hobby;

#[ExecuteConcurrently]
readonly class FiberAwaitDemoHobby implements Hobby
{
    public function __construct(
        private string $name,
    ) {}

    public function handle(): void
    {
        printf("[%s] %s requesting company profile\n", date('H:i:s'), $this->name);

        $profile = Async::await('company.profile', [
            'delay' => 0.75,
            'result' => [
                'company' => $this->name,
                'industry' => 'software',
            ],
        ]);

        printf(
            "[%s] %s received profile: %s\n",
            date('H:i:s'),
            $this->name,
            json_encode($profile['data'], JSON_THROW_ON_ERROR),
        );

        printf("[%s] %s requesting risk score\n", date('H:i:s'), $this->name);

        $risk = Async::await('company.risk-score', [
            'delay' => 1.25,
            'result' => [
                'company' => $this->name,
                'score' => 17,
            ],
        ]);

        printf(
            "[%s] %s received risk score: %s\n",
            date('H:i:s'),
            $this->name,
            json_encode($risk['data'], JSON_THROW_ON_ERROR),
        );

        printf("[%s] %s done\n", date('H:i:s'), $this->name);
    }
}
