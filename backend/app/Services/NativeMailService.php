<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class NativeMailService
{
    /**
     * Send an email using PHP's native mail() function.
     * No SMTP transport, Laravel mailer, or queue is used.
     */
    public function send(
        string $to,
        string $subject,
        string $html,
        array $attachments = [],
        ?string $from = null,
        ?string $fromName = null,
        ?string $replyTo = null,
    ): bool {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $from = $from ?: (string) env('MAIL_FROM_ADDRESS', 'support@banglesmart.com');
        $fromName = $fromName ?: (string) env('MAIL_FROM_NAME', 'BanglesMart');
        $replyTo = $replyTo ?: $from;

        $boundary = '=_BANGLESMART_' . bin2hex(random_bytes(12));

        $headers = [
            'MIME-Version: 1.0',
            'From: ' . $this->encodeHeader($fromName) . ' <' . $from . '>',
            'Reply-To: ' . $replyTo,
            'X-Mailer: PHP/' . PHP_VERSION,
            'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
        ];

        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $html . "\r\n";

        foreach ($attachments as $attachment) {
            $data = $attachment['data'] ?? '';
            $filename = $attachment['name'] ?? 'attachment';
            $mime = $attachment['mime'] ?? 'application/octet-stream';

            if (!is_string($data) || $data === '') {
                continue;
            }

            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: {$mime}; name=\"" . $this->escapeHeaderValue($filename) . "\"\r\n";
            $body .= "Content-Disposition: attachment; filename=\"" . $this->escapeHeaderValue($filename) . "\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($data)) . "\r\n";
        }

        $body .= "--{$boundary}--\r\n";

        $sent = @mail(
            $to,
            $this->encodeHeader($subject),
            $body,
            implode("\r\n", $headers)
        );

        if (!$sent) {
            Log::warning('PHP mail() could not send email.', [
                'to' => $to,
                'subject' => $subject,
            ]);
        }

        return $sent;
    }

    private function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function escapeHeaderValue(string $value): string
    {
        return str_replace(["\r", "\n", '"'], ['', '', ''], $value);
    }
}
