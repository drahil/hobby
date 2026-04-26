<?php

declare(strict_types=1);

namespace demos\sequentialApi;

final class FakeBlockingCompanyApi
{
    public static function profile(string $company): array
    {
        usleep(750_000);

        return [
            'company' => $company,
            'industry' => 'software',
        ];
    }

    public static function riskScore(string $company): array
    {
        usleep(1_250_000);

        return [
            'company' => $company,
            'score' => 17,
        ];
    }
}
