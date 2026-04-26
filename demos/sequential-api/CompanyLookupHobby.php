<?php

declare(strict_types=1);

namespace demos\sequentialApi;

use src\Attributes\OnQueue;
use src\Contracts\Hobby;

#[OnQueue('sequential-api-demo')]
readonly class CompanyLookupHobby implements Hobby
{
    public function __construct(
        private string $company,
    ) {}

    public function handle(): void
    {
        $this->log('requesting company profile');

        $profile = FakeBlockingCompanyApi::profile($this->company);

        $this->log('received profile ' . json_encode($profile, JSON_THROW_ON_ERROR));
        $this->log('requesting risk score');

        $risk = FakeBlockingCompanyApi::riskScore($this->company);

        $this->log('received risk score ' . json_encode($risk, JSON_THROW_ON_ERROR));
    }

    private function log(string $message): void
    {
        echo sprintf("[%s] %s: %s\n", date('H:i:s'), $this->company, $message);
    }
}
