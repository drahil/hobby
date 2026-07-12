<?php

declare(strict_types=1);

namespace demos\container;

final class FakeMailer implements Mailer
{
    public function sendWelcomeEmail(string $email): void
    {
        $path = dirname(__DIR__, 2) . '/storage/mailbox.txt';

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        $line = sprintf("[%s] Welcome email sent to %s\n", date('Y-m-d H:i:s'), $email);

        if (file_put_contents($path, $line, FILE_APPEND) === false) {
            throw new \RuntimeException("Failed to write to {$path}");
        }

        echo $line;
    }
}
