<?php

namespace App\Jobs;

use App\Services\SmsServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $to,
        public string $body,
        public ?int $userId = null
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(SmsServiceInterface $smsService): void
    {
        try {
            $smsService->send($this->to, $this->body, $this->userId);
            Log::info("SMS queued job completed successfully", [
                'to' => $this->to,
                'user_id' => $this->userId,
            ]);
        } catch (\Exception $e) {
            Log::error("SMS queued job failed", [
                'to' => $this->to,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SMS queued job permanently failed after all retries", [
            'to' => $this->to,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}
