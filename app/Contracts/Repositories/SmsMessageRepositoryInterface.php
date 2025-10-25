<?php

namespace App\Contracts\Repositories;

use App\DTOs\IncomingSmsData;
use App\Models\SmsMessage;

interface SmsMessageRepositoryInterface
{
    /**
     * Create an incoming SMS message record.
     */
    public function createIncoming(IncomingSmsData $data): SmsMessage;

    /**
     * Create an outgoing SMS message record.
     */
    public function createOutgoing(
        string $messageSid,
        string $to,
        string $from,
        string $body,
        string $status,
        string $accountSid,
        ?int $userId = null
    ): SmsMessage;

    /**
     * Update the status of an SMS message.
     */
    public function updateStatus(string $messageSid, string $status): bool;

    /**
     * Check if there are outgoing messages to a phone number.
     */
    public function hasOutgoingMessagesTo(string $phone): bool;
}
