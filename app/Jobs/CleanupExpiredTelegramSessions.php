<?php

namespace App\Jobs;

use App\Repositories\TelegramSessionRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CleanupExpiredTelegramSessions implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $repository = new TelegramSessionRepository;

        try {
            $cleanedCount = $repository->cleanupExpiredSessions();

            Log::info('Telegram session cleanup completed', [
                'cleaned_sessions' => $cleanedCount,
            ]);
        } catch (\Throwable $e) {
            Log::error('Telegram session cleanup failed: '.$e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
