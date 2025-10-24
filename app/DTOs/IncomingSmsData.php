<?php

namespace App\DTOs;

readonly class IncomingSmsData
{
    public function __construct(
        public string $messageSid,
        public string $from,
        public string $to,
        public string $body,
        public string $accountSid,
        public ?string $smsStatus = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            messageSid: $data['MessageSid'],
            from: $data['From'],
            to: $data['To'],
            body: $data['Body'],
            accountSid: $data['AccountSid'],
            smsStatus: $data['SmsStatus'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'MessageSid' => $this->messageSid,
            'From' => $this->from,
            'To' => $this->to,
            'Body' => $this->body,
            'AccountSid' => $this->accountSid,
            'SmsStatus' => $this->smsStatus,
        ];
    }
}
