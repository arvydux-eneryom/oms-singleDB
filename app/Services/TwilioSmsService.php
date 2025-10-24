<?php

namespace App\Services;

use App\DTOs\IncomingSmsData;
use App\DTOs\OutgoingSmsStatusData;
use App\Jobs\SendSmsJob;
use App\Repositories\SmsMessageRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Client;

class TwilioSmsService implements SmsServiceInterface
{
    protected Client $client;

    public function __construct(
        protected SmsMessageRepository $smsMessageRepository
    ) {
        $this->client = new Client(config('services.twilio.sid'), config('services.twilio.token'));
    }

    public function send($to, $body, ?int $userId = null): MessageInstance
    {
        $to = trim($to);
        try {
            $message = $this->client->messages->create(
                $to,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $body,
                    'statusCallback' => config('services.twilio.outgoing_sms_status_callback_url'),
                ]
            );

            $this->createSmsRecord($message, $userId);

            return $message;
        } catch (TwilioException $e) {
            Log::error('Twilio SMS send failed: '.$e->getMessage(), [
                'to' => $to,
                'body' => $body,
                'error_code' => $e->getCode(),
            ]);

            throw new \RuntimeException(
                'Failed to send SMS: '.$this->getUserFriendlyErrorMessage($e),
                $e->getCode(),
                $e
            );
        }
    }

    public function sendQueued(string $to, string $body, ?int $userId = null): void
    {
        $to = trim($to);

        SendSmsJob::dispatch($to, $body, $userId);

        Log::info('SMS queued for sending', [
            'to' => $to,
            'user_id' => $userId,
        ]);
    }

    protected function getUserFriendlyErrorMessage(TwilioException $e): string
    {
        $message = $e->getMessage();

        // Common Twilio error codes and user-friendly messages
        return match (true) {
            str_contains($message, 'not a valid phone number') => 'Invalid phone number format.',
            str_contains($message, 'Authenticate') || str_contains($message, 'credentials') => 'SMS service configuration error. Please contact support.',
            str_contains($message, 'insufficient funds') || str_contains($message, 'balance') => 'SMS service temporarily unavailable. Please try again later.',
            str_contains($message, 'unverified') => 'This phone number is not verified. Please use a verified number.',
            str_contains($message, 'Geographic permissions') => 'Cannot send SMS to this country.',
            str_contains($message, 'rate limit') => 'Too many SMS requests. Please try again in a few minutes.',
            default => 'Unable to send SMS. Please try again or contact support.',
        };
    }

    public function handleIncomingSms(IncomingSmsData $data): Response
    {
        $this->smsMessageRepository->createIncoming($data);

        Log::info('Incoming SMS saved to DB. SID: '.$data->messageSid);

        return response('', 200);
    }

    public function handleOutgoingSmsStatus(OutgoingSmsStatusData $data): Response
    {
        Log::info('Received Twilio status callback', $data->toArray());

        // Only update existing records (record should already exist from when SMS was sent)
        $updated = $this->smsMessageRepository->updateStatus($data->messageSid, $data->smsStatus);

        if ($updated) {
            Log::info('Outgoing SMS status updated in DB. SID: '.$data->messageSid.', Status: '.$data->smsStatus);
        } else {
            Log::warning('Attempted to update non-existent SMS. SID: '.$data->messageSid);
        }

        return response('', 200);
    }

    protected function createSmsRecord(MessageInstance $message, ?int $userId = null): void
    {
        $this->smsMessageRepository->createOutgoing(
            messageSid: $message->sid,
            to: $message->to,
            from: $message->from,
            body: $message->body,
            status: $message->status,
            accountSid: $message->accountSid,
            userId: $userId
        );

        Log::info('Outgoing SMS saved to DB. SID: '.$message->sid);
    }

    public function getAccountBalance(): array
    {
        try {
            // Fetch the current account balance
            $balance = $this->client->balance->fetch();

            $balanceValue = (float) $balance->balance;
            $currency = $balance->currency ?? 'USD';

            return [
                'balance' => (string) $balanceValue,
                'currency' => $currency,
                'formatted' => number_format($balanceValue, 2).' '.$currency,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch Twilio account balance: '.$e->getMessage(), [
                'error_code' => method_exists($e, 'getCode') ? $e->getCode() : null,
            ]);

            // Return a message that indicates the feature is not available
            return [
                'balance' => null,
                'currency' => null,
                'formatted' => 'Balance unavailable',
                'error' => $e->getMessage(),
            ];
        }
    }
}
