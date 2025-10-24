<?php

namespace App\DTOs;

readonly class OutgoingSmsStatusData
{
    public function __construct(
        public string $messageSid,
        public string $smsStatus,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            messageSid: $data['MessageSid'],
            smsStatus: $data['SmsStatus'],
        );
    }

    public function toArray(): array
    {
        return [
            'MessageSid' => $this->messageSid,
            'SmsStatus' => $this->smsStatus,
        ];
    }
}
