<?php

declare(strict_types=1);

namespace demos\concurrent;

use src\Async;
use src\Attributes\ExecuteConcurrently;
use src\Attributes\OnQueue;
use src\Contracts\Hobby;

#[OnQueue('concurrent-demo')]
#[ExecuteConcurrently]
readonly class CompanyLookupHobby implements Hobby
{
    public function __construct(
        private string $company,
    ) {}

    public function handle(): void
    {
        $this->log('requesting company profile');

        $profile = Async::await(
            new FakeCompanyApiRequest('profile', $this->company),
            timeoutSeconds: 5,
        );

        $this->log('received profile ' . json_encode($profile, JSON_THROW_ON_ERROR));
        $this->log('requesting risk score');

        $risk = Async::await(
            new FakeCompanyApiRequest('risk-score', $this->company),
            timeoutSeconds: 5,
        );

        $this->log('received risk score ' . json_encode($risk, JSON_THROW_ON_ERROR));
    }

    private function log(string $message): void
    {
        echo sprintf("[%s] %s: %s\n", date('H:i:s'), $this->company, $message);
    }
}
