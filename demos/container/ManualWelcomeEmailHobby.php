<?php

declare(strict_types=1);

namespace demos\container;

use src\Attributes\OnQueue;
use src\Contracts\Hobby;

#[OnQueue('container-demo')]
final readonly class ManualWelcomeEmailHobby implements Hobby
{
    public function __construct(
        private string $email,
    ) {}

    public function handle(): void
    {
        $mailer = new FakeMailer();

        $mailer->sendWelcomeEmail($this->email);
    }
}
