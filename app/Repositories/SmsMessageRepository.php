<?php

namespace App\Repositories;

use App\DTOs\IncomingSmsData;
use App\Models\SmsMessage;

class SmsMessageRepository
{
    public function createIncoming(IncomingSmsData $data): SmsMessage
    {
        return SmsMessage::create([
            'to' => $data->to,
            'from' => $data->from,
            'body' => $data->body,
            'status' => $data->smsStatus ?? 'received',
            'account_sid' => $data->accountSid,
            'sms_sid' => $data->messageSid,
            'message_type' => 'incoming',
        ]);
    }

    public function createOutgoing(
        string $messageSid,
        string $to,
        string $from,
        string $body,
        string $status,
        string $accountSid,
        ?int $userId = null
    ): SmsMessage {
        return SmsMessage::create([
            'sms_sid' => $messageSid,
            'to' => $to,
            'from' => $from,
            'body' => $body,
            'status' => $status,
            'account_sid' => $accountSid,
            'message_type' => 'outgoing',
            'user_id' => $userId,
        ]);
    }

    public function updateStatus(string $messageSid, string $status): bool
    {
        return SmsMessage::where('sms_sid', $messageSid)
            ->update(['status' => $status]) > 0;
    }

    public function hasOutgoingMessagesTo(string $phone): bool
    {
        return SmsMessage::where('to', $phone)
            ->where('message_type', 'outgoing')
            ->exists();
    }
}
