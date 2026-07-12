<?php

declare(strict_types=1);

namespace demos\container;

use src\Attributes\OnQueue;
use src\Contracts\Hobby;

#[OnQueue('container-demo')]
final readonly class InjectedWelcomeEmailHobby implements Hobby
{
    public function __construct(
        private string $email,
    ) {}

    public function handle(FakeMailer $mailer): void
    {
        $mailer->sendWelcomeEmail($this->email);
    }
}
