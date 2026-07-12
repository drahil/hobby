<?php

declare(strict_types=1);

namespace demos\container;

interface Mailer
{
    public function sendWelcomeEmail(string $email): void;
}
