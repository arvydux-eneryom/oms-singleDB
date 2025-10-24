<?php

namespace App\Jobs;

use App\Services\SmsServiceInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkSmsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;

    protected $body;

    protected $userId;

    public function __construct($to, $body, $userId)
    {
        $this->to = $to;
        $this->body = $body;
        $this->userId = $userId;
    }

    public function handle(SmsServiceInterface $smsService)
    {
        try {
            $message = $smsService->send($this->to, $this->body, $this->userId);

            Log::info('Bulk SMS sent successfully', [
                'to' => $this->to,
                'body' => $this->body,
                'user_id' => $this->userId,
                'sms_sid' => $message->sid,
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk SMS sending failed', [
                'error' => $e->getMessage(),
                'to' => $this->to,
                'body' => $this->body,
                'user_id' => $this->userId,
            ]);

            throw $e;
        }
    }
}
