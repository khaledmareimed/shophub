<?php

declare(strict_types=1);

namespace App\Services\Mailer;

final class FileMailboxMailer
{
    public function __construct(private string $mailboxDir)
    {
        if (!is_dir($this->mailboxDir)) {
            mkdir($this->mailboxDir, 0755, true);
        }
    }

    public function send(string $toEmail, string $subject, string $body): void
    {
        $safe = preg_replace('/[^a-zA-Z0-9._@-]/', '_', $toEmail);
        $file = $this->mailboxDir . '/' . $safe . '_' . gmdate('YmdHis') . '.eml';
        $raw = "To: $toEmail\nSubject: $subject\n\n$body\n";
        file_put_contents($file, $raw, LOCK_EX);
    }
}
